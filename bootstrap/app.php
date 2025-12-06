<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
                then: function () {
            // Register superadmin routes
            Route::middleware('web')
                ->prefix('superadmin')
                ->name('superadmin.')
                ->group(base_path('routes/superadmin.php'));

            // Register admin routes
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Register teacher routes
            Route::middleware('web')
                ->prefix('teacher')
                ->name('teacher.')
                ->group(base_path('routes/teacher.php'));

            // Register parent routes
            Route::middleware('web')
                ->prefix('parent')
                ->name('parent.')
                ->group(base_path('routes/parent.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register role middleware alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
