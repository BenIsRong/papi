#!/usr/bin/env php
<?php

require_once './base.php';

use Papi\Commands\Controller;
use Papi\Commands\Event;
use Papi\Commands\Model;
use Papi\Commands\Policy;
use Papi\Commands\Setup;
use Papi\Database as Database;

$db = new Database;

switch (strtolower($argv[1])) {
    case 'dev':
        exec('php -S localhost:8000 index.php');
        break;
    case 'init': // init
        new Setup;
        break;
    case 'create': // create
        switch (strtolower($argv[2])) {
            case 'table': // create table(s) from config.json
                $tables = $db->jsonToArray('config.json', 'tables');
                $errors = 0;
                switch (strtolower($argv[3])) {
                    case '-a':
                    case '--all': // create all tables, fail for table if already created
                        foreach ($tables as $key => $table) {
                            try {
                                $db->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '', true, false, false);
                            } catch (Throwable $t) {
                                $errors += 1;
                                echo "\n$table could not be created...";

                                continue;
                            }
                        }
                        echo "\nFinished creations of remaining tables with ".$errors.' fails and '.(count($tables) - $errors).' succeeded';
                        break;
                    case '-r':
                    case '--restricted': // create specified tables only
                        $names = explode(',', str_replace(' ', '', strtolower($argv[4])));
                        foreach ($names as $name) {
                            if (array_key_exists($name, $tables)) {
                                try {
                                    $db->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '', true, false, false);
                                } catch (Throwable $t) {
                                    $errors += 1;
                                    echo "\n$table could not be created...";

                                    continue;
                                }
                            } else {
                                $errors += 1;
                                echo "\n$name not found in config.json...!";

                                continue;
                            }
                        }
                        echo "\nFinished creations of remaining tables with ".$errors.' fails and '.(count($names) - $errors).' succeeded';
                        break;
                    case '-m':
                    case '--remaining': // create for remaining tables that have yet to be created
                        $success = 0;
                        $found = 0;
                        foreach ($tables as $key => $table) {
                            try {
                                if (! $db->tableExists($key, false)) {
                                    $db->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '', false, false, false);
                                    $success += 1;
                                } else {
                                    $found += 1;
                                }
                            } catch (Throwable $t) {
                                $errors += 1;
                                echo "\n$key could not be created...";

                                continue;
                            }
                        }
                        echo "\nFinished creations of remaining tables with $errors fails, $success succeeded and $found found";
                        break;
                }
                break;
            case 'controller': // create controller
                $model = null;
                $remArgs = array_slice($argv, 4);
                if (count($remArgs) > 0) {
                    foreach ($remArgs as $arg) {
                        switch (true) {
                            case str_starts_with($arg, '--model') | str_starts_with($arg, '-m'): // include model
                                if (substr_count($arg, '=') == 1) {
                                    $arg = explode('=', $arg);
                                    $arg = end($arg);
                                    if (strlen($arg) > 0) {
                                        new Model($arg);
                                        break;
                                    }
                                    $model = $arg;
                                }
                                $model = str_ends_with(strtolower($argv[3]), 'controller') ? substr($argv[3], 0, -10) : $argv[3];
                                new Model($model);
                                break;
                            case str_starts_with($arg, '--with_model') | str_starts_with($arg, '-wm'):
                                if (substr_count($arg, '=') == 1) {
                                    $arg = explode('=', $arg);
                                    $arg = end($arg);
                                    if (strlen($arg) > 0) {
                                        $model = $arg;
                                    } else {
                                        if (is_null($model)) {
                                            $model = str_ends_with(strtolower($argv[3]), 'controller') ? substr($argv[3], 0, -10) : $argv[3];
                                        }
                                    }
                                }
                                break;
                            case str_starts_with($arg, '--with_policy') | str_starts_with($arg, '-wp'):
                                if (substr_count($arg, '=') == 1) {
                                    $arg = explode('=', $arg);
                                    $arg = end($arg);
                                } else {
                                    $arg = str_ends_with(strtolower($argv[3]), 'controller') ? substr($argv[3], 0, -10) : $argv[3];
                                }
                                new Policy($arg);
                                break;
                            default:
                                break;
                        }
                    }
                }
                new Controller(str_ends_with(strtolower($argv[3]), 'controller') ? $argv[3] : $argv[3].'Controller', $model);
                break;
            case 'model': // create model
                $db = null;
                $remArgs = array_slice($argv, 4);
                if (count($remArgs) > 0) {
                    foreach ($remArgs as $arg) {
                        switch (true) {
                            case str_starts_with($arg, '--controller') | str_starts_with($arg, '-c'): // include controller
                                if (substr_count($arg, '=') == 1) {
                                    $arg = explode('=', $arg);
                                    $arg = end($arg);
                                    if (strlen($arg) > 0) {
                                        new Controller($arg.'Controller', $arg);
                                        break;
                                    }
                                }
                                new Controller($argv[3].'Controller', $argv[3]);
                                break;
                            case str_starts_with($arg, '--table') | str_starts_with($arg, '-t'): // include table
                                if (substr_count($arg, '=') == 1) {
                                    $arg = explode('=', $arg);
                                    $arg = end($arg);
                                    if (strlen($arg) > 0) {
                                        $db = $arg;
                                    }
                                    break;
                                }
                            case str_starts_with($arg, '--with_policy') | str_starts_with($arg, '-wp'):
                                if (substr_count($arg, '=') == 1) {
                                    $arg = explode('=', $arg);
                                    $arg = end($arg);
                                } else {
                                    $arg = $argv[3];
                                }
                                new Policy($arg);
                        }
                    }
                }
                new Model($argv[3], $db);
            case 'policy':
                new Policy($argv[3]);
                break;
            case 'event':
                if(str_contains(strtolower($argv[3]), 'event')){
                    new Event(str_ireplace("event", "", $argv[3]));
                }else{
                    new Event($argv[3]);
                }
                break;
        }
        break;
    case 'route':
        switch (strtolower($argv[2])) {
            case 'list':
                $routeFile = fopen('./papi/cache/routes', 'r');
                $routeFile = fread($routeFile, filesize('./papi/cache/routes'));
                $routeFile = gzuncompress($routeFile);
                $routes = json_decode($routeFile);
                foreach ($routes as $route) {
                    echo 'path: '.$route->path."\n";
                    echo 'method: '.$route->method."\n";
                    echo 'controller: '.str_replace('\\', '/', $route->controller[0]).' | '.$route->controller[1]."\n";
                    echo "=================================================================\n";
                }
        }
        break;
    case '--h':
    case 'help':
    case '-help':
        $helps = $db->jsonToArray('papi/resources/help.json');
        echo 'Commands are usually in the format of: php command [operation] [?-options] [?name]';
        echo "\nBelow is a list of available commands:";
        foreach ($helps['help'] as $help) {
            echo "\n=================================================================\n";
            if (array_key_exists('operations', $help)) {
                foreach ($help['operations'] as $operation) {
                    echo "\ncommand: ".$helps['command'].' '.$help['main']['operation'].' '.$operation['operation'].($help['main']['options'] ? ' [-options]' : '').($help['main']['name'] ? ' [name]' : '');
                    echo "\ndescription: ".$operation['description'];
                    if (array_key_exists('options', $operation)) {
                        echo "\n\n------------------------------------------------------------\n";
                        echo "\noptions:";
                        foreach ($operation['options'] as $option) {
                            echo "\nname: -".$option['name'].'(--'.$option['alt'].')'.($option['values'] ? '=[value]' : '');
                            echo "\ndescription: ".$option['description']."\n";
                        }
                        echo "\n\n------------------------------------------------------------\n";
                    }
                }
                echo "\n";
            } else {
                echo "\ncommand: ".$helps['command'].' '.($help['main']['operation'].$help['main']['options'] ? ' [-options]' : '').($help['main']['name'] ? ' [name]' : '');
                echo "\ndescription: ".$help['main']['description'];
                echo "\n";
            }
        }
        echo "=================================================================\n";
        break;
    default: // no command found
        echo 'the command '.$argv[1].' is not found';
        break;
}
