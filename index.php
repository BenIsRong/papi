<?php

require_once './base.php';

use Src\Router;

$router = new Router;

// add paths

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($path);
