<?php declare(strict_types=1);

return [
    ['GET', '/', ['App\Controllers\HomeController', 'articles']],
    ['GET', '/articles/{page:\d+}', ['App\Controllers\HomeController', 'article']],
    ['GET', '/article/{id}', ['App\Controllers\HomeController', 'article']],
    ['GET', '/author/{id}', ['App\Controllers\HomeController', 'author']],
];
