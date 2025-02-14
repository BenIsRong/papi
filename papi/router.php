<?php

namespace Papi;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, array $controller)
    {
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }

        $path = '/papi/api/'.$path;

        array_push($this->routes, [
            'path' => $path,
            'method' => $method,
            'controller' => $controller,
        ]);
    }

    public function dispatch(string $path)
    {
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }

        foreach ($this->routes as $route) {
            if ($path == $route['path'] && $route['method'] == strtoupper($_SERVER['REQUEST_METHOD'])) {
                [$class, $function] = $route['controller'];
                $controllerInstance = new $class;
                $controllerInstance->$function();
            }
        }
    }

    public function addCRUD(string $item, string $controller)
    {
        $cruds = [
            'createRecord' => 'POST',
            'readRecord' => 'GET',
            'readAllRecords' => 'GET',
            'updateRecord' => 'PUT',
            'deleteRecord' => 'DELETE',
            'clearAll' => 'DELETE',
        ];

        foreach ($cruds as $crud => $method) {
            $path = '/papi/api/'.$item;

            array_push($this->routes, [
                'path' => $crud == 'index' ? $path.'/all/' : $path.'/',
                'method' => $method,
                'controller' => [$controller, $crud],
            ]);
        }
    }

    public function listRoutes()
    {
        echo 'The routes are: ';

        foreach ($this->routes as $route) {
            echo '<br />'.$route['path'].' ('.$route['method'].')'.' | '.$route['controller'][0].', '.$route['controller'][1];
        }
    }
}
