<?php

namespace App\View\Composers;

use App\Models\ContactMessage;
use Illuminate\View\View;

class AdminLayoutComposer
{
    /**
     * Bind admin header data for every admin page.
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
            'unreadMessages' => ContactMessage::unread()->count(),
            'recentMessages' => ContactMessage::query()->latest('id')->limit(5)->get(),
        ]);
    }
}
