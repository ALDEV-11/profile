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
        // Without credentials the AWS SDK probes the EC2 metadata service.
        // Vercel and containers have none, so the probe blocks for 60s before
        // the upload fails. Disable it so storage errors surface immediately.
        if (getenv('AWS_EC2_METADATA_DISABLED') === false) {
            putenv('AWS_EC2_METADATA_DISABLED=true');
        }
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
