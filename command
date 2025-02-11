#!/usr/bin/env php
<?php

require_once('./base.php');

use Src\Commands\Setup;
use Src\Database as Database;
use Src\Commands\Model as CreateModel;
use Src\Commands\Controller as CreateController;

switch(strtolower($argv[1])){
    case 'init':
        new Setup;
        break;
    case 'create':
        switch(strtolower($argv[2])){
            case 'table':
                $tables = $db->jsonToArray('config.json', 'tables');
                $errors = 0;
                $db  = new Database;
                switch(strtolower($argv[3])){
                    case 'all':
                        foreach ($tables as $key => $table) {
                            try {
                                $db->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '');
                            } catch (Throwable $t) {
                                $errors += 1;
                                echo "\n$table could not be created...";
                                continue;
                            }
                        }
                        echo "\nFinished creations of remaining tables with ".$errors.' fails and '.(count($tables) - $errors).' succeeded';
                        break;
                    case 'restricted':
                        $names = explode(",", str_replace(" ", "", strtolower($argv[4])));
                        foreach($names as $name){
                            if(array_key_exists($name, $tables)){
                                try {
                                    $db->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '');
                                } catch (Throwable $t) {
                                    $errors += 1;
                                    echo "\n$table could not be created...";
                                    continue;
                                }
                            }else{
                                $errors += 1;
                                echo "\n$name not found in config.json...!";
                                continue;
                            }
                        }
                        echo "\nFinished creations of remaining tables with ".$errors.' fails and '.(count($names) - $errors).' succeeded';
                        break;
                    case 'remaining':
                        $success = 0;
                        $found = 0;
                        foreach ($tables as $key => $table) {
                            try {
                                if(!$db->tableExists($key)){
                                    $db->createTable($key, $table['columns'], (array_key_exists('pk', $table)) ? $table['pk'] : '', false);
                                    $success += 1;
                                }else{
                                    $found +=1;
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
            case 'controller':
                new CreateController(str_ends_with(strtolower($argv[3]), "controller") ? $argv[3] : $argv[3] . "Controller");
                $remArgs = array_slice($argv, 4);
                if(count($remArgs) > 0){
                    foreach($remArgs as $arg){
                        switch(true){
                            case str_starts_with($arg, '--model') | str_starts_with($arg, '-m'):
                                if(substr_count($arg, "=") == 1){
                                    $arg = explode("=", $arg);
                                    $arg = end($arg);
                                    if(strlen($arg) > 0){
                                        new CreateModel($arg);
                                        break;
                                    }
                                }
                                new CreateModel(str_ends_with(strtolower($argv[3]), "controller") ? substr($argv[3], 0, -10) : $argv[3]);
                                break;
                            default:
                                break;
                        }
                    }
                }
                break;
            case 'model':
                $db = null;
                $remArgs = array_slice($argv, 4);
                if(count($remArgs) > 0){
                    foreach($remArgs as $arg){
                        switch(true){
                            case str_starts_with($arg, '--controller') | str_starts_with($arg, '-c'):
                                if(substr_count($arg, "=") == 1){
                                    $arg = explode("=", $arg);
                                    $arg = end($arg);
                                    if(strlen($arg) > 0){
                                        new CreateController($arg . "Controller");
                                        break;
                                    }
                                }
                                new CreateController($argv[3] . "Controller");
                                break;
                            case str_starts_with($arg, '--db'):
                                if(substr_count($arg, "=") == 1){
                                    $arg = explode("=", $arg);
                                    $arg = end($arg);
                                    if(strlen($arg) > 0){
                                        $db = $arg;
                                    }
                                    break;
                                }

                                

                        }
                    }
                }
                new CreateModel($argv[3], $db);
        }
        break;
    default:
        echo 'the command ' . $argv[1] . ' is not found';
        break;
}