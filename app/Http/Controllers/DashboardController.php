<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard,
    ) {}

    /**
     * Display the admin dashboard overview.
     */
    public function index(): View
    {
        return view('admin.dashboard', array_merge($this->dashboard->overview(), [
            'adminName' => auth()->user()?->name,
        ]));
    }

    /**
     * Current unread message count (polled by the notification bell).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread' => $this->dashboard->unreadCount(),
        ]);
    }
}
