<?php

namespace App\Services;

use App\Models\ContactMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ContactMessageService
{
    /**
     * Messages per page in the inbox.
     */
    public const PER_PAGE = 10;

    /**
     * List messages, newest first, with optional search and read filter.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = ContactMessage::query()
            ->search($filters['q'] ?? null);

        if (($filters['status'] ?? null) === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->when($filters['status'] ?? null, function ($query, string $status) {
                $query->when($status === 'unread', fn ($query) => $query->unread())
                    ->when($status === 'read', fn ($query) => $query->read());
            });
        }

        return $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Messages the header notification dropdown shows (including trashed? no).
     */
    public function recent(int $limit = 5): Collection
    {
        return ContactMessage::query()->latest('id')->limit($limit)->get();
    }

    /**
     * Count of unread messages.
     */
    public function unreadCount(): int
    {
        return ContactMessage::unread()->count();
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(ContactMessage $message): void
    {
        $message->markAsRead();
    }

    /**
     * Mark a message as unread.
     */
    public function markAsUnread(ContactMessage $message): void
    {
        $message->markAsUnread();
    }

    /**
     * Soft delete a message (kept for history, excluded from inbox).
     */
    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }

    /**
     * Restore a soft-deleted message.
     */
    public function restore(int $id): ?ContactMessage
    {
        $message = ContactMessage::withTrashed()->findOrFail($id);
        $message->restore();

        return $message;
    }

    /**
     * Neighbours for prev/next navigation on the show page.
     *
     * @return array{prev: ?ContactMessage, next: ?ContactMessage}
     */
    public function neighbours(ContactMessage $message): array
    {
        $id = $message->id;

        $prev = ContactMessage::query()
            ->where('id', '<', $id)
            ->latest('id')
            ->first();

        $next = ContactMessage::query()
            ->where('id', '>', $id)
            ->oldest('id')
            ->first();

        return ['prev' => $prev, 'next' => $next];
    }
}
