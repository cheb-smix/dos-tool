<?php

if (isset($_GET["log"])) {
    $cmd = "tr -c -d '\n' < ./logs/{$_GET["log"]} | wc -c";
    echo (int) preg_replace("/[^\d]/", "", `$cmd`);
}