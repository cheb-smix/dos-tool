<?php

include_once "./lib/dos-master.php";

list($script, $url, $requestsCnt, $childrenCnt, $secondsCnt, $logFileName) = $argv;

$url = base64_decode($url);

$dm = new DosMaster([
    "url"           => $url,
    "requestsCnt"   => $requestsCnt,
    "childrenCnt"   => $childrenCnt,
    "secondsCnt"    => $secondsCnt,
    "logFileName"   => $logFileName,
]);

$dm->run();
