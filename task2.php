<?php
function readHttpLikeInput() {
    $f = fopen( 'php://stdin', 'r' );
    $store = "";
    $toread = 0;
    while( $line = fgets( $f ) ) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/',$line,$m)) 
            $toread=$m[1]*1; 
        if ($line == "\r\n") 
              break;
    }
    if ($toread > 0) 
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
    
$res = "HTTP/1.1" . " ". $statuscode . " " . $statusmessage . "\n";
$res = $res . "Date: " . date(DATE_RFC822) . "\n";
foreach($headers as $x => $x_value) {
    $res = $res . $x . ": " . $x_value . "\n";
}
$res = $res . "\n";
$res = $res . $body;
echo $res;
}

function processHttpRequest($method, $uri, $headers, $body) {
// If
// метод = GET
// uri = /sum?nums=.....разделенные запятыми числа....
// body = неважно
// headers = неважно
// sum = 5
// else if
// uri не начинается с /sum то надо выдать

    $statuscode = "";
    $statusmessage = "";
    $responsebody = "";

    // $uriOkStart = "/sum?nums=";
    // $uriWithoutStart = substr($uri, strpos($uri, $uriOkStart) + strlen($uriOkStart));
    // $pattern = '/^[0-9]+(?:,[0-9]+)*$/';
    // $uriPatternMatches = preg_match_all($pattern, $uriWithoutStart);
    // echo "\nMatches " . $uriPatternMatches . "\n";
    if ($method == "GET" && str_starts_with($uri, "/sum?nums=")) {
        $pattern = '/^[0-9]+(?:,[0-9]+)*$/';
        $uriWithoutStart = str_replace("/sum?nums=", "", $uri);
        $uriPatternMatches = preg_match_all($pattern, $uriWithoutStart);
        $responsebody = array_sum(explode(",", $uriWithoutStart));
        if ($uriPatternMatches == 1 && $responsebody == 5) {
            $statuscode = "200";
            $statusmessage = "OK";
        }
    } else {
        if ($method != "GET" || !strpos($uri, "?nums=")) {
            $statuscode = "400";
            $statusmessage = "Bad Request";
            $responsebody = "bad request";
        } else if (!str_starts_with($uri, "/sum")) {
            $statuscode = "404";
            $statusmessage = "Not Found";
            $responsebody = "not found";
        }
        
    }
    if (!empty($statuscode) && !empty($statusmessage) && !empty($responsebody)) {
        outputHttpResponse($statuscode, $statusmessage, $headers, $responsebody);
    }


    // if ($method == "GET" && str_starts_with($uri, $uriOkStart) && $uriPatternMatches == 1) {
    //     $statuscode = "200";
    //     $statusmessage = "OK";
    //     $responsebody = array_sum(explode(",", $uriWithoutStart));
    //     // outputHttpResponse($statuscode, $statusmessage, $headers, $responsebody);
    //     if ($responsebody == 5) {
    //         outputHttpResponse($statuscode, $statusmessage, $headers, $responsebody);
    //     }
    // }
    // if (!str_starts_with($uri, "/sum")){
    //     $statuscode = "404";
    //     $statusmessage = "Not Found";
    //     $responsebody = "not found";
    //     outputHttpResponse($statuscode, $statusmessage, $headers, $responsebody);
    // } 
    // if ((str_starts_with($uri, "/sum") && !strpos($uri, "?nums=")) || $method != "GET") {
    //     // 400 Bad Request
    //     $statuscode = "400";
    //     $statusmessage = "Bad Request";
    //     $responsebody = "bad request";
    //     outputHttpResponse($statuscode, $statusmessage, $headers, $responsebody);
    // }
    
    
}

function parseTcpStringAsHttpRequest($string) {
    $res = array();
    $arr = explode("\n", $string);
    
    $res["method"] = explode(" ", $arr[0])[0];
    $res["uri"] = explode(" ", $arr[0])[1];
    $body_index = array_search("", $arr) + 1;
    $res["body"] = $arr[$body_index];
    for($x = 0; $x < count($arr); $x++) {
        global $body_index;
        if ($x == 0 || $x == 1 || $x == $body_index || $x == $body_index - 1) {
            continue;
        }
        $header_arr = explode(":", $arr[$x]);
        if (count($header_arr) > 0 && array_key_exists(0, $header_arr) && array_key_exists(1, $header_arr)) {
            $res["headers"][$header_arr[0]] = $header_arr[1];
        }
    }
    return $res;
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);



?>