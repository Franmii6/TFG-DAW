<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',       // Rutas web (middleware web)
        api:      __DIR__.'/../routes/api.php',       // ⬅ Aquí añades tus rutas API
        commands: __DIR__.'/../routes/console.php',   // Rutas de consola, scheduler...
        health:   '/up',                              // Health check endpoint
        apiPrefix: '/api'                             // ⬅ Prefijo que aplicará a todas las rutas de api.php
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
