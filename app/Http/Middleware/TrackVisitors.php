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
     * The visitor insert is deferred until after the response has been sent,
     * so analytics never add a database round-trip to the page TTFB.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            $payload = $this->repository->payload($request);

            defer(fn () => $this->repository->recordPayload($payload));
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
