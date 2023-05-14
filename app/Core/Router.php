<?php declare(strict_types=1);

namespace App\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;

class Router
{
    public static function run(array $routes)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $router) use ($routes) {
            foreach ($routes as $route) {
                [$httpMethod, $url, $handler] = $route;
                $router->addRoute($httpMethod, $url, $handler);
            }
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                return 'Unknown';
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                [$class, $method] = $handler;

                $controller = new $class();
                $loader = new \Twig\Loader\FilesystemLoader('../App/Views');
                $twig = new \Twig\Environment($loader);

                $result = $controller->$method($twig, $vars);

                if ($result instanceof \App\Views\View) {
                    $template = $twig->load($result->getTemplate() . '.twig');
                    return $template->render($result->getData());
                }

                return $result;
        }
        return null;
    }
}
