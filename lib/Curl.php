<?php

class Curl
{
    static $httpcode = 0;
    static $location;
    static $result;
    static $verboseLog = "";
    static $command = "";

    /**
     * @param $url
     * @param $params
     * @param $type
     * @param $dataType
     * @param $headers
     * @return bool|string
     */
    public static function init(string $url, array $params = [], string $type = "GET", string $dataType = "json", array $headers = [], array $options = [], bool $debug = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($type != "GET") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $type == "JSON" ? json_encode($params) : $params);
            if ($type == "JSON") {
                $headers[] = "Content-Type: application/json";
            }
        }

        if ($headers) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);      
        if ($options) {
            foreach ($options as $option => $value) {
                curl_setopt($curl, $option, $value);
            }
        } 

        if ($debug) {
            $streamVerboseHandle = fopen('php://temp', 'w+');
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_STDERR, $streamVerboseHandle);
        }
        
        self::$result = curl_exec($curl);
        self::$httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        self::$location = curl_getinfo($curl, CURLINFO_REDIRECT_URL);
        curl_close($curl);

        if ($debug) {
            rewind($streamVerboseHandle);
            self::$verboseLog = htmlspecialchars(stream_get_contents($streamVerboseHandle));
            self::$command = "curl -v --request $type " . implode('', array_map(function ($header) {
                return "--header '$header' ";
            }, $headers)) . "'$url'" . " " . ($type == "POST" ? implode('', array_map(function($value, $key) {
                return "--form '$key=\"$value\"' ";
            }, $params, array_keys($params))) : "");
        }

        if ($dataType == "json") {
            try {
                self::$result = json_decode(self::$result, true) ?? self::$result;
            } catch (Exception $e) { }
        }

        return self::$result;
    }

    public static function code()
    {
        return self::$httpcode;
    }

    public static function body()
    {
        return self::$result;
    }

    public static function redirect()
    {
        return self::$location;
    }

    public static function verbose()
    {
        return self::$verboseLog;
    }

    public static function request()
    {
        return self::$command;
    }
}
