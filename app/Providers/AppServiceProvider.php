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

        // Register once on the layout. The header and sidebar are included
        // from it, so this prevents the unread/recent queries from running
        // twice per admin page.
        View::composer('admin.layouts.admin-layout', AdminLayoutComposer::class);
    }
}
