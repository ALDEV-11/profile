<?php

namespace App\Models;

use App\Support\ClearsContentCaches;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'name', 'email', 'subject', 'message', 'is_read'])]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use ClearsContentCaches, HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Messages that have not been read yet.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    /**
     * Messages that have already been read.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    /**
     * Filter messages by name, email, or subject.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function (Builder $query, string $term) {
            $query->where(function (Builder $inner) use ($term) {
                $inner->whereLike('name', "%{$term}%", caseSensitive: false)
                    ->orWhereLike('email', "%{$term}%", caseSensitive: false)
                    ->orWhereLike('subject', "%{$term}%", caseSensitive: false);
            });
        });
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return true;
        }

        return $this->forceFill(['is_read' => true])->save();
    }

    /**
     * Mark the message as unread.
     */
    public function markAsUnread(): bool
    {
        if (! $this->is_read) {
            return true;
        }

        return $this->forceFill(['is_read' => false])->save();
    }
}
