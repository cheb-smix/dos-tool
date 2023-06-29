<?php

include_once "./Curl.php";

list($script, $url, $xlhdagent, $host) = $argv;

$url = base64_decode($url);
$xlhdagent = base64_decode($xlhdagent);
$timestamp = microtime(true);

$randomDeviceId = sha1("device" . rand(1000, 9000000) . microtime(true));
preg_replace('/device_id":"[a-zA-Z0-9\-]+"/', 'device_id":"' . $randomDeviceId . '"', $xlhdagent);

$headers = [
    'X-LHD-Agent: ' . stripslashes($xlhdagent)
];

if ($host != "0.0.0.0") {
    $headers[] = 'Host: ' . $host;
}

Curl::init($url, [], "GET", "text", $headers, [], true);

$body = Curl::body();

$responsetime = microtime(true) - $timestamp;

$data = [
    "started"   => $timestamp,
    "ended"     => $timestamp + $responsetime,
    "time"      => $responsetime,
    "xlhdagent" => $xlhdagent,
    "url"       => $url,
    "status"    => Curl::code(),
    "request"   => Curl::request(),
    "is_json"   => (substr($body, 0, 1) == "{" && substr($body, -1, 1) == "}"),
];

print json_encode($data, 256) . "\n";
