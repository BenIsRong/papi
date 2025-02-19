<?php

namespace Papi;

use DateTime;

if (!defined('LOG_SEV_EMERGENCY')) {
    define('LOG_SEV_EMERGENCY', 0);
}
if (!defined('LOG_SEV_ALERT')) {
    define('LOG_SEV_ALERT', 1);
}
if (!defined('LOG_SEV_CRITICAL')) {
    define('LOG_SEV_CRITICAL', 2);
}
if (!defined('LOG_SEV_ERROR')) {
    define('LOG_SEV_ERROR', 3);
}
if (!defined('LOG_SEV_WARNING')) {
    define('LOG_SEV_WARNING', 4);
}
if (!defined('LOG_SEV_NOTICE')) {
    define('LOG_SEV_NOTICE', 5);
}
if (!defined('LOG_SEV_INFORMATIONAL')) {
    define('LOG_SEV_INFORMATIONAL', 6);
}
if (!defined('LOG_SEV_DEBUG')) {
    define('LOG_SEV_DEBUG', 7);
}

abstract class Logger extends Base
{
    public static function logger($severity = LOG_SEV_INFORMATIONAL, string $msg = '', bool $client = false, bool $server = true)
    {
        $datetime = new DateTime();
        $datetime = $datetime->format('Y-m-d\TH:i:sp');
        $log =
        $log = [
            "client_ip" => ($client ? $_SERVER['REMOTE_ADDR'] : ''),
            "client_port" => ($client ? $_SERVER['REMOTE_PORT'] : ''),
            "server_ip" => ($server ? $_SERVER['SERVER_ADDR'] : ''),
            "server_port" => ($server ? $_SERVER['SERVER_PORT'] : ''),
            "machine" => ($client ? $_SERVER['HTTP_USER_AGENT'] : ''),
            "datetime" => $datetime,
            "msg" => $msg,
        ];

        switch ($severity) {
            case 0:
                $log['severity'] = "EMERGENCY";
                break;
            case 1:
                $log['severity'] = "ALERT";
                break;
            case 2:
                $log['severity'] = "CRITICAL";
                break;
            case 3:
                $log['severity'] = "ERROR";
                break;
            case 4:
                $log['severity'] = "WARNING";
                break;
            case 5:
                $log['severity'] = "NOTICE";
                break;
            case 6:
                $log['severity'] = "INFORMATIONAL";
                break;
            case 7:
                $log['severity'] = "DEBUG";
                break;
        }

        $logType = parent::jsonToArray('config.json', 'log_type');

        switch (strtolower(is_null($logType) ? 'csv' : $logType)) {
            case 'csv':
                self::toCSV($log);
                break;
            case 'json':
                self::toJSON($log);
                break;
            case 'txt':
                self::toTXT($log);
                break;
            default:
                self::toCSV($log);
                break;
        }
    }

    private static function toCSV(array $log)
    {
        if (!file_exists("log.csv")) {
            fopen("log.csv", 'a');
        }

        if (filesize('log.csv') == 0) {
            file_put_contents("log.csv", "severity,client_ip,client_port,server_ip,server_port,machine,datetime,msg\n", FILE_APPEND);
        }

        $log = implode(",", array_values($log));

        file_put_contents('log.csv', $log . "\n", FILE_APPEND);
    }

    private static function toJSON(array $log)
    {
        if (!file_exists("log.json")) {
            fwrite(fopen("log.json", 'w'), json_encode([$log]));
        } else {
            $prevLogs = file_get_contents('log.json', true);
            $prevLogs = json_decode($prevLogs, true);
            array_push($prevLogs, $log);
            fwrite(fopen("log.json", 'w'), json_encode($prevLogs));
        }
    }

    private static function toTXT(array $log)
    {
        if (!file_exists("log.txt")) {
            fopen("log.txt", 'a');
        }

        file_put_contents("log.txt", ("[" . $log['severity'] . "][" . $log['datetime'] . "](client => " . $log['client_ip'] . ":" . $log['client_port'] . ")[" . $log['machine'] ."](server => " . $log['server_ip'] . ":" . $log['server_port'] . "): " . $log['msg']) . "\n", FILE_APPEND);
    }
}
