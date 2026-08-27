<?php

namespace App\Http\Middleware;

use App\Repositories\VisitorRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    public function __construct(
        protected VisitorRepository $repository,
    ) {}

    /**
     * Handle an incoming request.
     *
     * Records anonymous analytics data for every public page load.
     * Runs after the response is generated so tracking never slows
     * down the actual page rendering, and never crashes the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            $this->repository->record($request);
        }

        return $response;
    }

    /**
     * Only track anonymous GET requests against public pages.
     */
    protected function shouldTrack(Request $request): bool
    {
        return $request->isMethod('GET')
            && ! $request->user()
            && ! $request->is('222/*', 'up', 'api/*');
    }
}
