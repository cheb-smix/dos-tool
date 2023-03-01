<?php

include_once "./lib/Math.php";

$running = false;
$logFileName = "";
$url = "";
$requestsCnt = 100;
$childrenCnt = 10;
$secondsCnt = 0;

if (isset($_GET["action"])) {
    if ($_GET["action"] == "break") {
        `bash killer.sh`;
        if (isset($_GET["log"])) {
            header("Location: index.php?log=" . $_GET["log"]);
        }
        exit;
    }
    if ($_GET["action"] == "sleep") {
        `php sleeper.php`;
        exit;
    }
}

if (!empty($_POST)) {
    $running = true;
    $url            = isset($_POST["url"]) ? $_POST["url"] : "";
    $requestsCnt    = isset($_POST["requestsCnt"])  ? (int) $_POST["requestsCnt"] : 100;
    $childrenCnt    = isset($_POST["childrenCnt"])  ? (int) $_POST["childrenCnt"] : 10;
    $secondsCnt     = isset($_POST["secondsCnt"])   ? (int) $_POST["secondsCnt"] : 0;
    $logFileName    = "dm-" . date("Y-m-d-H-i-s-u") . ".log";

    file_put_contents("./logs/$logFileName", json_encode([
        "url"           => $url,
        "requestsCnt"   => $requestsCnt,
        "childrenCnt"   => $childrenCnt,
        "secondsCnt"    => $secondsCnt,
        "executeStarted"=> date("Y-m-d H:i:s"),
    ], 256) . "\n");
    
    $encodedUrl = base64_encode($url);

    $cmd = "cd " . __DIR__ . "; php dm.php $encodedUrl $requestsCnt $childrenCnt $secondsCnt $logFileName > /dev/null 2>/dev/null &";
    `$cmd`;
} else {
    if (empty($_GET)) {
        $check = `ps ax | grep "php dm.php"`;
        preg_match("/(\d+) (\d+) (\d+) (dm-[0-9\-]+.log)/", $check, $matches);
        if ($matches) {
            $running = true;
            list($cmd, $requestsCnt, $childrenCnt, $secondsCnt, $logFileName) = $matches;
        }
    }
}

if (!$running && isset($_GET["log"])) {
    $logFileName = $_GET["log"];
}

?>
<html>
    <head>
        <title>DOS TOOL</title>
        <style>
            @font-face {
                font-family: EuropeExt;
                src: url(europe_ext.ttf) format("truetype");
            }
            body {
                background: #333;
                color: #ddd;
                font-family: "Open Sans", "Candara", "Cambria";
                padding: 1%;
            }
            .content {
                width: 70%;
                margin: 5px 15%;
                position: relative;
            }
            form {
                position: relative;
                padding: 0px 25%;
            }
            input {
                display: block;
                position: relative;
                padding: 10px;
                margin: 10px;
                border-radius: 5px;
                width: 100%;
                background: #aaa;
            }
            form > span {
                position: relative;
                font-size: 14px;
                display: block;
                color: #cf0;
            }
            #progressbar {
                position: relative;
                height: 30px;
                margin: 20px;
                border-radius: 5px;
                width: 100%;
                background: rgba(255,255,255,0.7);
            }
            #progressbar > span {
                overflow: hidden;
                font-size: 10px;
                position: absolute;
                text-align: right;
                color: #333;
                top: 0;
                border-radius: 5px;
                background: #0cf;
                z-index: 10;
                height: 30px;
                transition: all 0.3s ease;
            }
            table {
                width: 100%;
                color: white;
                margin: 20px;
                border: 1px solid #777;
                box-shadow: 3px 3px 5px #111;
            }
            th {
                text-align: left;
                border-bottom: 1px solid #555;
            }
            th:first-child {
                width: 40%;
            }
            td, th {
                padding: 4px;
            }
            .btn, input[type=submit] {
                color: #fff;
                background-image: linear-gradient(135deg, #a900ec, #6b00f7);
                border-color: #6b00f7;
                padding-left: 3em;
                padding-right: 3em;
                box-shadow: 0px 0px 0px #e300f7;
                transition: all 0.3s ease;
                padding: 10px;
                border-radius: 5px;
                text-decoration: none;
            }
            .btn:hover, .btn:active, .btn:focus, input[type=submit]:hover, input[type=submit]:active, input[type=submit]:focus {
                color: #ddd;
                box-shadow: 0px 0px 5px #0066ff;
            }
            .btn {
                margin: 20px;
            }
            a {
                color: #ccc;
                text-decoration: none;
            }
            a:hover {
                color: white;
            }
            #logo {
                color: #999;
                font: bold 1.4em EuropeExt, Cambria;
                position: fixed;
                width: 15%;
                text-align: center;
            }
            .logo {
                display: inline-block;
                height: 20vh; 
                width: 0; 
                padding-left: 20vh;
                margin: 0;
                border-radius: 50%;
                background-image: url(./logo.jpg);
                background-position: center;
                background-size: 100% auto;
                box-shadow: inset 0px 0px 30px #333, inset 0px 0px 10px #333;
            }
            .logo-container {
                text-align: center;
            }
            #timingbar {
                text-align: right;
            }
        </style>
        <script>
            file_get_contents  = (url) => {	
                var req = null;
                try { req = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {
                    try { req = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {
                        try { req = new XMLHttpRequest(); } catch(e) {}
                    }
                }
                if (req == null) throw new Error('XMLHttpRequest not supported');

                req.open("GET", url, false);
                req.send(null);

                return req.responseText;
            }

            let requestsCnt = <?=$requestsCnt?>;

            updateProgress = () => {
                let lncnt = file_get_contents("progressbar.php?log=<?=$logFileName?>") - 0;
                let perc = Math.round(lncnt * 100 / requestsCnt);

                document.querySelector("#progressbar > span").innerHTML = `${lncnt} / ${requestsCnt}`;
                document.querySelector("#progressbar > span").style.width = perc + "%";

                if (lncnt >= requestsCnt) {
                    location.href = "index.php?log=<?=$logFileName?>";
                }
            }

            let started = 0;

            updateTiming = () => {
                if (!started) started = (new Date()).getTime();
                let currentTime = (new Date()).getTime();
                document.querySelector("#timingbar").innerHTML = (currentTime - started) / 1000;
            }

        </script>
    </head>
    <body>
        <div id="logo">DOS TOOL</div>
        <div class="content">
            <div class="logo-container">
                <div class="logo"></div>
            </div>
        <?php
            if ($running) {
                ?>
                <h3>Proccessing...</h3>
                <div id="progressbar"><span>0 / <?=$requestsCnt?></span></div>
                <div id="timingbar">0</div>
                <a href="./index.php?action=break&log=<?=$logFileName?>" class="btn">Остановить</a>
                <script>
                    updateProgress();
                    setInterval(updateProgress, 200);
                    setInterval(updateTiming, 50);
                </script>
                <?php
            } else {
                if ($logFileName) {

                    $data = file("./logs/$logFileName");
                    $data = array_map(function ($item) {
                        return json_decode(preg_replace("/[\r\n]/", "", $item));
                    }, $data);

                    $status_map = [
                        "TOTAL" => 0
                    ];
                    $timing_arr = [];
                    $start_time = 0;
                    $end_time = 0;
                    $uniqueUserAgents = [];
                    $fineResponseCnt = 0;

                    $technical = [];
                    if (isset($data[0]->url)) {
                        $technical = array_shift($data);
                    }

                    foreach ($data as $i => $row) {
                        if (!isset($status_map[$row->status])) $status_map[$row->status] = 0;
                        $status_map[$row->status]++;
                        $status_map["TOTAL"]++;
                        $timing_arr[] = $row->time;
                        if ($i == 0) {
                            $start_time = $row->started;
                        }
                        if ($i == count($data) - 1) {
                            $end_time = $row->ended;
                        }
                        $uniqueUserAgents[$row->xlhdagent] = $row->xlhdagent;
                        if ($row->is_json) {
                            $fineResponseCnt++;
                        }
                    }
                    ?>
                    <a href="./index.php" class="btn">Main page</a>
                    <table>
                        <thead><tr><th>STATUS</th><th>CNT</th></tr></thead>
                        <tbody>
                            <?php 
                            foreach ($status_map as $code => $cnt) {
                                ?><tr><td><?=$code?></td><td><?=$cnt?></td></tr><?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <table>
                        <thead><tr><th>Indicator</th><th>Value (seconds)</th></tr></thead>
                        <tbody>
                            <tr><td>Minimum</td><td><?=Math::min($timing_arr, 3)?></td></tr>
                            <tr><td>Maximum</td><td><?=Math::max($timing_arr, 3)?></td></tr>
                            <tr><td>Arithmetic</td><td><?=Math::arithmetic($timing_arr, 3)?></td></tr>
                            <tr><td>Quadratic</td><td><?=Math::quadratic($timing_arr, 3)?></td></tr>
                            <tr><td>Median</td><td><?=Math::median($timing_arr, 3)?></td></tr>
                            <tr><td>Summary</td><td><?=Math::sum($timing_arr, 3)?></td></tr>
                        </tbody>
                    </table>
                    <table>
                        <thead><tr><th>Parameter</th><th>Value</th></tr></thead>
                        <tbody>
                            <?php if ($technical) { ?>
                                <tr><td>Request URL</td><td><?=$technical->url?></td></tr>
                                <tr><td>Request Count</td><td><?=$technical->requestsCnt?></td></tr>
                                <tr><td>Number of child processes</td><td><?=$technical->childrenCnt?></td></tr>
                                <tr><td>Time to execute</td><td><?=$technical->secondsCnt?></td></tr>
                            <?php } ?>
                            <tr><td>Log FileName</td><td><?=$logFileName?></td></tr>
                            <tr><td>Started at</td><td><?=date("Y-m-d H:i:s", $start_time)?></td></tr>
                            <tr><td>Ended at</td><td><?=date("Y-m-d H:i:s", $end_time)?></td></tr>
                            <tr><td>Time elapsed (sec.)</td><td><?=$end_time - $start_time?></td></tr>
                            <tr><td>Unique X-LHD-Agents used</td><td><?=count($uniqueUserAgents)?></td></tr>
                            <tr><td>Response is JSON (percents)</td><td><?=round($fineResponseCnt*100/$status_map["TOTAL"], 2)?>%</td></tr>
                        </tbody>
                    </table>
                    <?php

                } else {
                    ?>
                    <form action="./index.php" method="POST">
                        <span>Request URL</span>
                        <input type="text"   name="url"         id="url"         value="<?=$url?>"          placeholder="Request URL">
                        <span>Requests Count</span>
                        <input type="number" name="requestsCnt" id="requestsCnt" value="<?=$requestsCnt?>"  placeholder="Requests Count">
                        <span>Number of child processes</span>
                        <input type="number" name="childrenCnt" id="childrenCnt" value="<?=$childrenCnt?>"  placeholder="Number of child processes">
                        <span>Time to execute</span>
                        <input type="number" name="secondsCnt"  id="secondsCnt"  value="<?=$secondsCnt?>"   placeholder="Time to execute">
                        <span> &nbsp; </span>
                        <input type="submit" value="Execute">
                    </form>
                    <br><br>
                    <h3>Last logs</h3>
                    <?php

                    $logs = array_reverse(scandir("./logs"));

                    for ($i = 0; $i < 10; $i++) {
                        if (!stristr($logs[$i], ".log")) break;
                        $f = fopen("./logs/" . $logs[$i], 'r');
                        $line = json_decode(fgets($f));
                        fclose($f);
                        $label = "";
                        if (isset($line->executeStarted)) {
                            $label .= $line->executeStarted;
                        } else {
                            $label .= $logs[$i];
                        }
                        if (isset($line->url)) {
                            $label .= " ($line->url)";
                        }
                        if (isset($line->requestsCnt)) {
                            $label .= " | $line->requestsCnt / $line->childrenCnt / $line->secondsCnt";
                        }
                        ?><p><a href="index.php?log=<?=$logs[$i]?>"><?=$label?></a></p><?php
                    }
                }
            }
        ?>
        </div>
    </body>
</html>

