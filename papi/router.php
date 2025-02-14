<?php

namespace Papi;

class Router extends Base
{
    private array $routes = [];

    private string $cachePath = __DIR__.'/cache/routes';

    public function __construct()
    {
        if (file_exists($this->cachePath)) {
            unlink($this->cachePath);
        }
    }

    public function add(string $method, string $path, array $controller)
    {
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }

        $path = '/papi/api'.(substr($path, 0, 1) == '/' ? '' : '/').$path;

        array_push($this->routes, [
            'path' => $path,
            'method' => $method,
            'controller' => $controller,
        ]);

        $this->addToCache($this->cachePath, $this->routes);

    }

    public function dispatch(string $path)
    {
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }

        foreach ($this->routes as $route) {
            if (preg_match('/{(.*?)}/', $route['path'], $matches)) {
                $explodedRoute = explode('/', $route['path']);
                $explodedPath = explode('/', $path);
                $routePos = array_search($matches[0], $explodedRoute);
                $pathValue = $explodedPath[$routePos];
                unset($explodedRoute[$routePos]);
                unset($explodedPath[$routePos]);
                $implodeRoute = implode('/', $explodedRoute);
                $implodePath = implode('/', $explodedPath);

                if ($implodePath == $implodeRoute && $route['method'] == strtoupper($_SERVER['REQUEST_METHOD'])) {
                    [$class, $function] = $route['controller'];
                    $controllerInstance = new $class;
                    $controllerInstance->$function($this->request(), $pathValue);
                }
            }
            if ($path == $route['path'] && $route['method'] == strtoupper($_SERVER['REQUEST_METHOD'])) {
                [$class, $function] = $route['controller'];
                $controllerInstance = new $class;
                $controllerInstance->$function($this->request());
            }
        }
    }

    public function addCRUD(string $model, string $controller)
    {
        $cruds = [
            'createRecord' => 'POST',
            'readRecord' => 'GET',
            'readAllRecords' => 'GET',
            'updateRecord' => 'PUT',
            'deleteRecord' => 'DELETE',
            'clearAll' => 'DELETE',
        ];

        $nonIds = [
            'createRecord',
            'readAllRecords',
            'clearAll',
        ];

        foreach ($cruds as $crud => $method) {
            $model = strtolower($model);
            $model = str_contains($model, '\\') ? explode('\\', $model) : explode('//', $model);
            $model = end($model);
            $path = '/papi/api/'.$model.(in_array($crud, $nonIds) ? '' : '/{id}');

            array_push($this->routes, [
                'path' => $crud == 'index' ? $path.'/all/' : $path.'/',
                'method' => $method,
                'controller' => [$controller, $crud],
            ]);
        }
        $this->addToCache($this->cachePath, $this->routes);
    }

    public function listRoutes()
    {
        echo 'The routes are: '."\n";

        $routeFile = fopen($this->cachePath, 'r');
        $routeFile = fread($routeFile, filesize($this->cachePath));
        $routeFile = gzuncompress($routeFile);
        $routes = json_decode($routeFile);
        foreach ($routes as $route) {
            echo 'path: '.$route->path."\n";
            echo 'method: '.$route->method."\n";
            echo 'controller: '.$route->controller[0].' | '.$route->controller[1]."\n";
            echo "=================================================================\n";
        }
    }

    /**
     * Get request data either through form and/or params
     * Each of the key has to be different
     *
     * @return array
     */
    private function request(int $options = REQUEST_ALL)
    {
        switch ($options) {
            case REQUEST_ALL:
                return array_merge($_POST, $_GET);
                break;
            case REQUEST_FORM_ONLY:
                return $_POST;
                break;
            case REQUEST_PARAMS_ONLY:
                return $_GET;
                break;
        }
    }
}
