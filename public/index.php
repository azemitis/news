<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

$routes = require_once __DIR__ . '/../routes.php';

$result = Router::run($routes);

echo $result;
