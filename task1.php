
<?php

// не обращайте на эту функцию внимания
// она нужна для того чтобы правильно считать входные данные
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
echo(json_encode($http, JSON_PRETTY_PRINT)) . "\n";

$example ="GET /doc/test.html HTTP/1.1
Host: www.test101.com
Accept: image/gif, image/jpeg, */*
Accept-Language: en-us
Accept-Encoding: gzip, deflate
User-Agent: Mozilla/4.0
Content-Length: 35

bookId=12345&author=Tan+Ah+Teck
";
// $gg = parseTcpStringAsHttpRequest($example);
// echo(json_encode($gg, JSON_PRETTY_PRINT)) . "\n";
?>