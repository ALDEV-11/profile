<?php

namespace App\View\Composers;

use App\Services\DashboardService;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function __construct(
        protected DashboardService $dashboard,
    ) {}

    /**
     * Bind admin layout data for every admin page.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();
        $profile = $user?->profile;

        $view->with([
            'adminName' => $user?->name,
            'adminAvatar' => ! empty($profile?->profile_image)
                ? asset($profile->profile_image)
                : asset('dist-dashboard/assets/img/avatars/1.png'),
            'unreadMessages' => $this->dashboard->unreadCount(),
            'recentMessages' => $this->dashboard->recentMessages(),
        ]);
    }
}
