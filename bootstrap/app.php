<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return tap(Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'level_akses' => \App\Http\Middleware\PastikanLevelAkses::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create(), function (Application $app): void {
        $folderPublik = env('APP_PUBLIC_PATH');

        if (is_string($folderPublik) && $folderPublik !== '') {
            $app->usePublicPath($app->basePath(trim($folderPublik, '/\\')));
        }
    });
