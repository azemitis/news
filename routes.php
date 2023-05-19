<?php declare(strict_types=1);

return [
    ['GET', '/', ['App\Controllers\HomeController', 'index']],
//    ['GET', '/articles/{page:\d+}', ['App\Controllers\HomeController', 'show']],
    ['GET', '/article/{id}', ['App\Controllers\HomeController', 'show']],
    ['GET', '/user/{id}', ['App\Controllers\HomeController', 'user']],
];
