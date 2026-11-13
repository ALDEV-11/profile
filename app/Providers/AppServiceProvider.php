<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // URL::forceScheme('https');a

        View::composer(
            ['admin.layouts.admin-header', 'admin.layouts.admin-sidebar'],
            AdminLayoutComposer::class,
        );
    }
}
