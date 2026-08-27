<?php

namespace App\Services;

use App\Models\SocialMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SocialMediaService
{
    /**
     * Maximum number of social links a user may have.
     */
    public const MAX_LINKS = 5;

    /**
     * Default Boxicons class for each supported platform.
     *
     * @var array<string, string>
     */
    public const PLATFORM_ICONS = [
        'GitHub' => 'bx bxl-github',
        'Instagram' => 'bx bxl-instagram',
        'LinkedIn' => 'bx bxl-linkedin',
        'Twitter/X' => 'bx bxl-twitter',
        'Portfolio' => 'bx bx-briefcase',
        'Custom' => 'bx bx-link',
    ];

    /**
     * List the user's social media links, ordered for display.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(int $userId, array $filters = []): \Illuminate\Support\Collection
    {
        return SocialMedia::query()
            ->where('user_id', $userId)
            ->when($filters['q'] ?? null, function (Builder $query, string $q) {
                $query->where(function (Builder $inner) use ($q) {
                    $inner->where('platform', 'like', "%{$q}%")
                        ->orWhere('url', 'like', "%{$q}%");
                });
            })
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Persist a new social media link.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $userId, array $data): SocialMedia
    {
        $data['user_id'] = $userId;
        $data['order'] = $data['order'] ?? $this->nextOrder($userId);

        return SocialMedia::create($data);
    }

    /**
     * Update an existing social media link.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SocialMedia $socialMedia, array $data): SocialMedia
    {
        $socialMedia->update($data);

        return $socialMedia;
    }

    /**
     * Delete a social media link (hard delete — there is no uploaded file).
     */
    public function delete(SocialMedia $socialMedia): void
    {
        $socialMedia->forceDelete();
    }

    /**
     * Next display order value for the user.
     */
    public function nextOrder(int $userId): int
    {
        return (int) SocialMedia::query()
            ->where('user_id', $userId)
            ->max('order') + 1;
    }

    /**
     * Icon class for a platform, falling back to the custom icon.
     */
    public function defaultIcon(?string $platform, ?string $customIcon = null): string
    {
        if (! $platform) {
            return $customIcon ?: self::PLATFORM_ICONS['Custom'];
        }

        return self::PLATFORM_ICONS[$platform] ?? ($customIcon ?: self::PLATFORM_ICONS['Custom']);
    }
}
