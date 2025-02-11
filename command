#!/usr/bin/env php
<?php
include_once './commands/init.php';
use Setup as Setup;

switch(strtolower($argv[1])){
    case 'init':
        new Setup;
        break;
    case 'create':
        switch(strtolower($argv[2])){
            case 'table':
                break;
        }
        break;
    default:
        echo 'the command ' . $argv[1] . ' is not found';
        break;
}