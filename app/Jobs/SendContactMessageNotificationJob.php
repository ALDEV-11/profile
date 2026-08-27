<?php

namespace App\Jobs;

use App\Mail\SendContactMessageNotification;
use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendContactMessageNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ContactMessage $contactMessage,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminEmail = Profile::query()->value('email')
            ?? config('mail.from.address');

        Mail::to($adminEmail)->send(
            new SendContactMessageNotification($this->contactMessage)
        );
    }
}
