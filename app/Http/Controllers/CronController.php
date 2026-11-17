<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

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
     * One-off runtime check for object storage configuration. Reports only
     * booleans, lengths and hosts, never the credential values themselves.
     */
    public function storageCheck(Request $request): JsonResponse
    {
        $this->ensureAuthorized($request);

        $config = config('filesystems.disks.public');
        $key = (string) ($config['key'] ?? '');
        $secret = (string) ($config['secret'] ?? '');

        $report = [
            'driver' => $config['driver'] ?? null,
            'root' => $config['root'] ?? null,
            'url' => $config['url'] ?? null,
            'key_set' => $key !== '',
            'key_length' => strlen($key),
            'secret_set' => $secret !== '',
            'secret_length' => strlen($secret),
            'bucket_set' => filled($config['bucket'] ?? null),
            'region' => $config['region'] ?? null,
            'endpoint_host' => parse_url((string) ($config['endpoint'] ?? ''), PHP_URL_HOST),
            'path_style' => (bool) ($config['use_path_style_endpoint'] ?? false),
            'config_cached' => app()->configurationIsCached(),
            'metadata_disabled' => getenv('AWS_EC2_METADATA_DISABLED') === 'true',
        ];

        if ($key === '' || $secret === '' || empty($config['bucket'])) {
            $report['upload'] = 'skipped: credentials incomplete';

            return response()->json($report);
        }

        $path = 'diagnostics/storage-check-'.Str::uuid().'.txt';

        try {
            $disk = Storage::disk('public');
            $disk->put($path, 'ok');
            $url = $disk->url($path);
            $response = Http::timeout(15)->get($url);

            $report['upload'] = 'ok';
            $report['public_status'] = $response->status();
            $report['public_body_ok'] = $response->body() === 'ok';

            $disk->delete($path);
        } catch (Throwable $e) {
            $report['upload'] = 'error';
            $report['error'] = class_basename($e).': '.Str::limit($e->getMessage(), 200);
        }

        return response()->json($report);
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
