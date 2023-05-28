<?php declare(strict_types=1);

return [
    ['GET', '/', ['App\Controllers\HomeController', 'index']],
    ['GET', '/article/{id}', ['App\Controllers\HomeController', 'show']],
    ['GET', '/user/{id}', ['App\Controllers\HomeController', 'user']],
    ['GET', '/register', ['App\Controllers\HomeController', 'register']],
    ['GET', '/login', ['App\Controllers\HomeController', 'login']],
];
