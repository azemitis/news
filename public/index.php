<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use GuzzleHttp\Client;

$routes = require_once __DIR__ . '/../routes.php';

$httpClient = new Client();

$controller = new HomeController($httpClient);

$result = Router::run($routes, $controller);

echo $result;
