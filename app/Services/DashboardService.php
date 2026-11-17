<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Repositories\VisitorRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Cache lifetime in seconds. Short enough to stay fresh, long enough to
     * remove the repeated queries from every admin page view.
     */
    private const TTL = 60;

    public function __construct(
        protected VisitorRepository $visitors,
    ) {}

    /**
     * Payload for the admin dashboard overview.
     *
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $data = Cache::remember('admin.dashboard.overview', now()->addSeconds(self::TTL), function () {
            return [
                'totalVisitors' => $this->visitors->total(),
                'totalMessages' => ContactMessage::query()->count(),
                'unreadMessages' => ContactMessage::unread()->count(),
                'galleryCount' => Gallery::query()->count(),
                'recentMessages' => $this->recentMessageRows(),
                'visitorTrend' => $this->visitors->dailyCounts(30),
            ];
        });

        $data['recentMessages'] = ContactMessage::hydrate($data['recentMessages']);

        return $data;
    }

    /**
     * Unread message count shown in the header, sidebar and dashboard.
     */
    public function unreadCount(): int
    {
        return Cache::remember('admin.messages.unread', now()->addSeconds(self::TTL), function () {
            return ContactMessage::unread()->count();
        });
    }

    /**
     * Latest five messages shown in the header and dashboard.
     *
     * @return Collection<int, ContactMessage>
     */
    public function recentMessages(): Collection
    {
        $rows = Cache::remember('admin.messages.recent', now()->addSeconds(self::TTL), function () {
            return $this->recentMessageRows();
        });

        return ContactMessage::hydrate($rows);
    }

    /**
     * Raw rows for the cached payload. Eloquent instances do not survive
     * unserialization with the container's optimized autoloader.
     *
     * @return array<int, array<string, mixed>>
     */
    private function recentMessageRows(): array
    {
        return ContactMessage::query()
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (ContactMessage $message) => $message->getAttributes())
            ->all();
    }
}
