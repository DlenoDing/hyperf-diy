<?php

declare(strict_types=1);

use Hyperf\HttpServer\Router\Router;

Router::get(
    '/favicon.ico',
    function () {
        return '';
    }
);
//health
Router::get(
    '/',
    function () {
        return '';
    }
);

Router::addServer(
    'ws',
    function () {
        Router::get('/', 'App\WebSocket\Enter\WebSocketEnter');
    }
);
