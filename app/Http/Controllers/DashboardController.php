<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Repositories\VisitorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard overview.
     */
    public function index(VisitorRepository $visitors): View
    {
        $user = auth()->user();

        $data = [
            'totalVisitors' => $visitors->total(),
            'totalMessages' => ContactMessage::query()->count(),
            'unreadMessages' => ContactMessage::unread()->count(),
            'galleryCount' => Gallery::query()->count(),
            'recentMessages' => ContactMessage::query()->latest('id')->limit(5)->get(),
            'visitorTrend' => $visitors->dailyCounts(30),
            'adminName' => $user?->name,
        ];

        return view('admin.dashboard', $data);
    }

    /**
     * Current unread message count (polled by the notification bell).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread' => ContactMessage::unread()->count(),
        ]);
    }
}
