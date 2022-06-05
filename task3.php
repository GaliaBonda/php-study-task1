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
if (is_array($headers)) {
  foreach($headers as $x => $x_value) {
    $res = $res . $x . ": " . $x_value . "\n";
}  
}
$res = $res . "\n";
$res = $res . $body;
echo $res;
}

// POST /api/checkLoginAndPassword HTTP/1.1
// Accept: */*
// Content-Type: application/x-www-form-urlencoded
// User-Agent: Mozilla/4.0
// Content-Length: 35

// login=student&password=12345


function processHttpRequest($method, $uri, $headers, $body) {
    $statuscode = "200";
    $statusmessage = "OK";
    $responsebody = '<h1 style="color:green">FOUND</h1>';
    
    if ($method != "POST" || $uri != "/api/checkLoginAndPassword" || 
    $headers["Content-Type"] != "application/x-www-form-urlencoded") {
        echo "\n method " . $method . " " . $uri . " " . $headers["Content-Type"] . "\n";
        $statuscode = "400";
    $statusmessage = "Bad Request";
    $responsebody = 'bad request';
    }
    $login = "";
    $password = "";
    // echo "\n body " . $body . "\n";
    $body_arr = explode("&", $body);
    // echo "\n body arr " . $body_arr[0] . " " . $body_arr[1] . "\n";
    $userAuthentication = array("login" => explode("=", $body_arr[0])[1], "password" => explode("=", $body_arr[1])[1]);
    echo "\n" . $userAuthentication["login"] . " " . $userAuthentication["password"] . "\n";
    echo "\n" . file_get_contents(__DIR__ . "\assets\password.txt", false) . "\n";
    $responseHeaders = array("Server" => "Apache/2.2.14 (Win32)", 
    // Content-Length: 1
    "Connection" => "Closed",
    "Content-Type" => $headers["Content-Type"]    
    );
    outputHttpResponse($statuscode, $statusmessage, $responseHeaders, $body);
    
    
}

function parseTcpStringAsHttpRequest($string) {
    $res = array("method" => "", "uri" => "", "headers" => array(), "body" => "");
    $arr = explode("\n", $string);
    // echo "\n" . explode(" ", $arr[0])[0] . " " . explode(" ", $arr[0])[1];
    $res["method"] = count(explode(" ", $arr[0])) > 0 ? explode(" ", $arr[0])[0] : "";
    $res["uri"] = count(explode(" ", $arr[0])) > 1 ? explode(" ", $arr[0])[1] : "";
    $body_index = array_search("", $arr) + 1;
    $res["body"] = $arr[$body_index];
    for($x = 0; $x < count($arr); $x++) {
        global $body_index;
        if ($x == 0 || $x == $body_index || $x == $body_index - 1) {
            continue;
        }
        $header_arr = explode(":", $arr[$x]);
        if (count($header_arr) > 0 && array_key_exists(0, $header_arr) && array_key_exists(1, $header_arr)) {
            // echo "\n" . $header_arr[0] . " " . $header_arr[1] . "\n";
            $res["headers"][$header_arr[0]] = trim($header_arr[1]);
            
        }
    }
    return $res;
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);



?>