<?php

namespace App\Services;

use App\Enums\DeviceType;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Colour per device type (Chart.js friendly).
     *
     * @var array<string, string>
     */
    public const DEVICE_COLORS = [
        'desktop' => '#7367f0',
        'mobile' => '#00cfe8',
        'tablet' => '#ff9f43',
    ];

    /**
     * Referrer buckets shown on the source bar chart.
     *
     * @var array<int, string>
     */
    private const REFERRER_BUCKETS = ['Direct', 'Google', 'Social Media', 'Referral'];

    /**
     * Hosts treated as social media referrers.
     *
     * @var array<int, string>
     */
    private const SOCIAL_HOSTS = [
        'facebook.com', 'fb.com', 'instagram.com', 'twitter.com', 'x.com',
        'linkedin.com', 'youtube.com', 'tiktok.com', 'whatsapp.com', 't.me',
    ];

    /**
     * The application's display timezone (Indonesia).
     */
    public const DISPLAY_TIMEZONE = 'Asia/Jakarta';

    /**
     * Resolve a date range from the request parameters.
     *
     * Bounds are defined in the display timezone then converted to UTC,
     * because the visitors table stores timestamps in UTC.
     *
     * @return array{from: ?Carbon, to: ?Carbon, label: string, key: string}
     */
    public function resolveRange(?string $range = null, ?string $start = null, ?string $end = null): array
    {
        $range = $range ?: '30d';

        if ($range === 'all') {
            return ['from' => null, 'to' => null, 'label' => 'All', 'key' => 'all'];
        }

        if ($range === '7d') {
            $from = now(self::DISPLAY_TIMEZONE)->subDays(6)->startOfDay();
            $to = now(self::DISPLAY_TIMEZONE)->endOfDay();

            return [
                'from' => $from->copy()->setTimezone('UTC'),
                'to' => $to->copy()->setTimezone('UTC'),
                'label' => '7 Hari Terakhir',
                'key' => '7d',
            ];
        }

        if ($range === 'custom' && $start && $end) {
            $from = Carbon::parse($start, self::DISPLAY_TIMEZONE)->startOfDay();
            $to = Carbon::parse($end, self::DISPLAY_TIMEZONE)->endOfDay();

            if ($from->lte($to)) {
                return [
                    'from' => $from->copy()->setTimezone('UTC'),
                    'to' => $to->copy()->setTimezone('UTC'),
                    'label' => 'Kustom',
                    'key' => "custom.{$start}.{$end}",
                ];
            }
        }

        $from = now(self::DISPLAY_TIMEZONE)->subDays(29)->startOfDay();
        $to = now(self::DISPLAY_TIMEZONE)->endOfDay();

        return [
            'from' => $from->copy()->setTimezone('UTC'),
            'to' => $to->copy()->setTimezone('UTC'),
            'label' => '30 Hari Terakhir',
            'key' => '30d',
        ];
    }

    /**
     * Combined analytics payload used by both the page and the AJAX endpoint.
     *
     * @return array<string, mixed>
     */
    public function overview(?string $range = null, ?string $start = null, ?string $end = null): array
    {
        $resolved = $this->resolveRange($range, $start, $end);

        return Cache::remember("analytics.overview.{$resolved['key']}", now()->addHour(), function () use ($resolved) {
            [$from, $to] = [$resolved['from'], $resolved['to']];

            $previous = $this->previousWindow($from, $to);

            return [
                'range' => $resolved['label'],
                'cards' => [
                    'visitors' => $this->card(
                        $this->uniqueVisitors($from, $to),
                        $this->uniqueVisitors($previous['from'], $previous['to'])
                    ),
                    'pageViews' => $this->card(
                        $this->pageViews($from, $to),
                        $this->pageViews($previous['from'], $previous['to'])
                    ),
                    'newToday' => [
                        'value' => $this->uniqueVisitors(...$this->todayUtcBounds()),
                        'delta' => 0,
                        'trend' => 'flat',
                    ],
                ],
                'trend' => $this->visitorTrend($from, $to),
                'devices' => $this->deviceBreakdown($from, $to),
                'referrers' => $this->topReferrers($from, $to),
                'pages' => $this->topPages($from, $to),
            ];
        });
    }

    /**
     * Build a {value, delta, trend} card payload.
     *
     * @return array{value: int, delta: int, trend: string}
     */
    protected function card(int $current, int $previous): array
    {
        $delta = $previous === 0
            ? ($current > 0 ? 100 : 0)
            : (int) round((($current - $previous) / $previous) * 100);

        $trend = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');

        return ['value' => $current, 'delta' => $delta, 'trend' => $trend];
    }

    /**
     * The window of equal length immediately before the given one.
     *
     * @return array{from: ?Carbon, to: ?Carbon}
     */
    protected function previousWindow(?Carbon $from, ?Carbon $to): array
    {
        if (! $from) {
            return ['from' => null, 'to' => null];
        }

        $days = $from->diffInDays($to ?? now()) + 1;

        return [
            'from' => $from->copy()->subDays($days),
            'to' => $from->copy()->subDay()->endOfDay(),
        ];
    }

    /**
     * Unique visitors (distinct IPs) within the given range.
     */
    public function uniqueVisitors(?Carbon $from, ?Carbon $to): int
    {
        return (int) Visitor::query()
            ->when($from, fn ($query) => $query->where('visited_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('visited_at', '<=', $to))
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * Total page views within the given range.
     */
    public function pageViews(?Carbon $from, ?Carbon $to): int
    {
        return (int) Visitor::query()
            ->when($from, fn ($query) => $query->where('visited_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('visited_at', '<=', $to))
            ->count();
    }

    /**
     * Per-day unique visitor counts (grouped by the display timezone),
     * oldest to newest, zero-filled.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function visitorTrend(?Carbon $from, ?Carbon $to): array
    {
        $from ??= Visitor::query()->min('visited_at');
        $to ??= Visitor::query()->max('visited_at');

        if (! $from || ! $to) {
            return ['labels' => [], 'data' => []];
        }

        $rows = Visitor::query()
            ->select(['ip_address', 'visited_at'])
            ->where('visited_at', '>=', $from)
            ->where('visited_at', '<=', $to)
            ->get();

        $ipsByDay = [];

        foreach ($rows as $row) {
            $day = $row->visited_at->setTimezone(self::DISPLAY_TIMEZONE)->toDateString();
            $ipsByDay[$day][(string) $row->ip_address] = true;
        }

        $labelFrom = Carbon::parse($from)->setTimezone(self::DISPLAY_TIMEZONE)->startOfDay();
        $labelTo = Carbon::parse($to)->setTimezone(self::DISPLAY_TIMEZONE)->startOfDay();

        $labels = [];
        $data = [];

        for ($day = $labelFrom->copy(); $day->lte($labelTo); $day->addDay()) {
            $labels[] = $day->format('d M');
            $data[] = count($ipsByDay[$day->toDateString()] ?? []);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * UTC bounds for "today" in the display timezone.
     *
     * @return array{Carbon, Carbon}
     */
    protected function todayUtcBounds(): array
    {
        $today = now(self::DISPLAY_TIMEZONE);

        return [
            $today->copy()->startOfDay()->setTimezone('UTC'),
            $today->copy()->endOfDay()->setTimezone('UTC'),
        ];
    }

    /**
     * Visits grouped by device type.
     *
     * @return array{labels: array<int, string>, data: array<int, int>, colors: array<int, string>}
     */
    public function deviceBreakdown(?Carbon $from, ?Carbon $to): array
    {
        $labels = ['Desktop', 'Mobile', 'Tablet'];
        $keys = array_map(fn (DeviceType $type) => $type->value, DeviceType::cases());

        $rows = Visitor::query()
            ->selectRaw('device_type, COUNT(*) as count')
            ->when($from, fn ($query) => $query->where('visited_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('visited_at', '<=', $to))
            ->groupBy('device_type')
            ->pluck('count', 'device_type');

        $data = array_map(fn (string $key) => (int) ($rows[$key] ?? 0), $keys);
        $colors = array_map(fn (string $key) => self::DEVICE_COLORS[$key] ?? '#8f9bb3', $keys);

        return compact('labels', 'data', 'colors');
    }

    /**
     * Visits bucketed by referrer source, highest first.
     *
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public function topReferrers(?Carbon $from, ?Carbon $to): array
    {
        $rows = Visitor::query()
            ->selectRaw('referer, COUNT(*) as count')
            ->when($from, fn ($query) => $query->where('visited_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('visited_at', '<=', $to))
            ->groupBy('referer')
            ->get();

        $buckets = array_fill_keys(self::REFERRER_BUCKETS, 0);

        foreach ($rows as $row) {
            $buckets[$this->categorizeReferer($row->referer)] += (int) $row->count;
        }

        uasort($buckets, fn (int $a, int $b) => $b <=> $a);

        return [
            'labels' => array_keys($buckets),
            'data' => array_values($buckets),
        ];
    }

    /**
     * Top visited pages with views and unique visitors.
     *
     * @return array<int, array{page: string, views: int, unique: int}>
     */
    public function topPages(?Carbon $from, ?Carbon $to, int $limit = 10): array
    {
        return Visitor::query()
            ->selectRaw('page_visited, COUNT(*) as views, COUNT(DISTINCT ip_address) as unique_visitors')
            ->when($from, fn ($query) => $query->where('visited_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('visited_at', '<=', $to))
            ->groupBy('page_visited')
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'page' => $row->page_visited,
                'views' => (int) $row->views,
                'unique' => (int) $row->unique_visitors,
            ])
            ->all();
    }

    /**
     * Map a raw referer URL to a display bucket.
     */
    protected function categorizeReferer(?string $referer): string
    {
        if ($referer === null || $referer === '') {
            return 'Direct';
        }

        $host = strtolower((string) parse_url($referer, PHP_URL_HOST));
        $siteHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));

        if ($host === '' || $host === $siteHost) {
            return 'Direct';
        }

        if (str_contains($host, 'google.')) {
            return 'Google';
        }

        foreach (self::SOCIAL_HOSTS as $social) {
            if (str_contains($host, $social)) {
                return 'Social Media';
            }
        }

        return 'Referral';
    }
}
