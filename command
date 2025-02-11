#!/usr/bin/env php
<?php
use Src\Commands\Setup;
use Src\DB;
use Src\Commands\Controller as CreateController;

$db = new DB();

switch(strtolower($argv[1])){
    case 'init':
        new Setup;
        break;
    case 'create':
        switch(strtolower($argv[2])){
            case 'table':
                $tables = $db->jsonToArray('config.json', 'tables');
                $errors = 0;
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
                $controller = new CreateController($argv[3]);
        }
        break;
    default:
        echo 'the command ' . $argv[1] . ' is not found';
        break;
}