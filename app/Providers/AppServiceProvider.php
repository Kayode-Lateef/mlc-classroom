<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\DateHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add custom Blade directives for role checking
        Blade::if('superadmin', function () {
            return auth()->check() && auth()->user()->role === 'superadmin';
        });

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->role === 'admin';
        });

        Blade::if('teacher', function () {
            return auth()->check() && auth()->user()->role === 'teacher';
        });

        Blade::if('parent', function () {
            return auth()->check() && auth()->user()->role === 'parent';
        });

    }
}
