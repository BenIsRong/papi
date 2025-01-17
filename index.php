<?php
include('./api/UserController.php');
use UserController as UserController;

class Router {
    private array $routes = [];

    public function add(string $method, string $path, array $controller){
        if(substr($path, -1) != '/'){
            $path = $path . '/';
        }

        array_push($this->routes, [
            'path' => $path,
            'method' => $method,
            'controller' => $controller,
        ]);
    }

    public function dispatch(string $path){
        if(substr($path, -1) != '/'){
            $path = $path . '/';
        }
        foreach ($this->routes as $route){
            if($path == $route['path'] && $route['method'] == strtoupper($_SERVER['REQUEST_METHOD'])){
                [$class, $function] = $route['controller'];
                $controllerInstance = new $class;
                $controllerInstance->$function();
            }
        }
    }
}

$router = new Router();

$router->add('POST', '/api/api/test', [UserController::class, 'createUser']);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($path);