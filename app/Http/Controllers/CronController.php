<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CronController extends Controller
{
    /**
     * Delete visitor records older than the retention window.
     */
    public function visitorsCleanup(Request $request): Response
    {
        $this->ensureAuthorized($request);

        Artisan::call('visitors:cleanup');

        return response()->noContent();
    }

    /**
     * Cheap database ping used by an external scheduler to keep the
     * Neon compute warm. Vercel Hobby only allows daily crons.
     */
    public function keepalive(Request $request): Response
    {
        $this->ensureAuthorized($request);

        DB::select('select 1');

        return response()->noContent();
    }

    /**
     * Vercel sends CRON_SECRET as a Bearer token.
     */
    protected function ensureAuthorized(Request $request): void
    {
        $secret = (string) config('services.cron.secret');

        abort_if(
            $secret === '' || ! hash_equals($secret, (string) $request->bearerToken()),
            403,
        );
    }
}
