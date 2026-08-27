<?php

namespace App\Http\Controllers;

use App\Events\ContactMessageReceived;
use App\Http\Requests\ContactMessageRequest;
use App\Jobs\SendContactMessageNotificationJob;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * Store a new contact message submitted from the public contact form.
     *
     * Validates the request, persists the message, and queues an email
     * notification to the site admin. Responds with JSON for AJAX handling.
     */
    public function store(ContactMessageRequest $request): JsonResponse
    {
        $contactMessage = ContactMessage::create([
            'user_id' => auth()->id(),
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'is_read' => false,
        ]);

        SendContactMessageNotificationJob::dispatch($contactMessage);
        ContactMessageReceived::dispatch($contactMessage);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your message received.',
        ], 201);
    }
}
