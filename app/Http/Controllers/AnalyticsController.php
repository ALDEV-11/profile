<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analytics,
    ) {}

    /**
     * Display the analytics dashboard for the given range.
     */
    public function index(Request $request): View
    {
        $data = $this->analytics->overview(
            $request->query('range'),
            $request->query('start'),
            $request->query('end'),
        );

        $activeRange = $request->query('range', '30d');

        return view('admin.analytics.index', compact('data', 'activeRange'));
    }

    /**
     * Combined JSON payload refreshed by the date-range filter (AJAX).
     */
    public function data(Request $request): JsonResponse
    {
        return response()->json(
            $this->analytics->overview(
                $request->query('range'),
                $request->query('start'),
                $request->query('end'),
            )
        );
    }
}
