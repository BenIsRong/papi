<?php

namespace Papi;

use DateTime;

if (!defined('LOG_EMERGENCY')) {
    define('LOG_EMERGENCY', 0);
}
if (!defined('LOG_ALERT')) {
    define('LOG_ALERT', 1);
}
if (!defined('LOG_CRITICAL')) {
    define('LOG_CRITICAL', 2);
}
if (!defined('LOG_ERROR')) {
    define('LOG_ERROR', 3);
}
if (!defined('LOG_WARNING')) {
    define('LOG_WARNING', 4);
}
if (!defined('LOG_NOTICE')) {
    define('LOG_NOTICE', 5);
}
if (!defined('LOG_INFORMATIONAL')) {
    define('LOG_INFORMATIONAL', 6);
}
if (!defined('LOG_DEBUG')) {
    define('LOG_DEBUG', 7);
}

abstract class Logger extends Base
{
    public static function logger($severity = LOG_INFORMATIONAL, string $msg = '', bool $client = false, bool $server = true)
    {
        if (!file_exists("log.csv")) {
            fopen("log.csv", 'a');
        }

        if (filesize('log.csv') == 0) {
            file_put_contents("log.csv", "severity,client_ip,client_port,server_ip,server_port,machine,datetime,msg\n", FILE_APPEND);
        }

        $datetime = new DateTime();
        $datetime = $datetime->format('Y-m-dTH:i:sp');
        $log =
        $log = [
            ($client ? $_SERVER['REMOTE_ADDR'] : ''),
            ($client ? $_SERVER['REMOTE_PORT'] : ''),
            ($server ? $_SERVER['SERVER_ADDR'] : ''),
            ($server ? $_SERVER['SERVER_PORT'] : ''),
            ($client ? $_SERVER['HTTP_USER_AGENT'] : ''),
            $datetime,
            $msg,
        ];
        switch ($severity) {
            case LOG_EMERGENCY:
                array_unshift($log, "EMERGENCY");
                break;
            case LOG_ALERT:
                array_unshift($log, "ALERT");
                break;
            case LOG_CRITICAL:
                array_unshift($log, "CRITICAL");
                break;
            case LOG_ERROR:
                array_unshift($log, "ERROR");
                break;
            case LOG_WARNING:
                array_unshift($log, "WARNING");
                break;
            case LOG_NOTICE:
                array_unshift($log, "NOTICE");
                break;
            case LOG_INFORMATIONAL:
                array_unshift($log, "INFORMATIONAL");
                break;
            case LOG_DEBUG:
                array_unshift($log, "DEBUG");
                break;
        }

        file_put_contents('log.csv', implode(',', $log) . "\n", FILE_APPEND);
    }
}
