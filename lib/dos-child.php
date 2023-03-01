<?php

include_once "./Curl.php";

list($script, $url, $xlhdagent) = $argv;

$url = base64_decode($url);
$xlhdagent = base64_decode($xlhdagent);
$timestamp = microtime(true);

Curl::init($url, [], "GET", "text", [
    "X-LHD-Agent: $xlhdagent"
]);

$body = Curl::body();

$responsetime = microtime(true) - $timestamp;

$data = [
    "started"   => $timestamp,
    "ended"     => $timestamp + $responsetime,
    "time"      => $responsetime,
    "xlhdagent" => $xlhdagent,
    "url"       => $url,
    "status"    => Curl::code(),
    "is_json"   => (substr($body, 0, 1) == "{" && substr($body, -1, 1) == "}"),
];

print json_encode($data, 256) . "\n";
