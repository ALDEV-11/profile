<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(
        protected ContactMessageService $messages,
    ) {}

    /**
     * Display the inbox listing.
     */
    public function index(Request $request): View
    {
        $messages = $this->messages->list($request->only('q', 'status'));

        return view('admin.contact-messages.index', compact('messages'));
    }

    /**
     * Display a single message (marks it as read).
     */
    public function show(ContactMessage $message): View
    {
        $this->messages->markAsRead($message);

        ['prev' => $prev, 'next' => $next] = $this->messages->neighbours($message);

        return view('admin.contact-messages.show', compact('message', 'prev', 'next'));
    }

    /**
     * Toggle a message to read/unread (AJAX friendly).
     */
    public function toggleRead(Request $request, ContactMessage $message): JsonResponse|RedirectResponse
    {
        if ((bool) $request->boolean('is_read')) {
            $this->messages->markAsRead($message);
        } else {
            $this->messages->markAsUnread($message);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_read' => $message->is_read,
            ]);
        }

        return back()->with('success', $message->is_read ? 'Pesan ditandai sudah dibaca.' : 'Pesan ditandai belum dibaca.');
    }

    /**
     * Soft delete a message.
     */
    public function destroy(Request $request, ContactMessage $message): JsonResponse|RedirectResponse
    {
        $this->messages->delete($message);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.messages.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * Restore a soft-deleted message.
     */
    public function restore(int $id): RedirectResponse
    {
        $this->messages->restore($id);

        return redirect()->route('admin.messages.index')
            ->with('success', 'Pesan berhasil dipulihkan.');
    }
}
