<?php

namespace App\Repositories;

use App\Enums\DeviceType;
use App\Models\Visitor;
use App\Services\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorRepository
{
    /**
     * Record a single anonymous page visit.
     *
     * Never throws: tracking must never break the request cycle.
     *
     * @return bool whether the record was persisted
     */
    public function record(Request $request): bool
    {
        return $this->recordPayload($this->payload($request));
    }

    /**
     * Build the visitor row from the request.
     *
     * The payload is captured before the response is sent so the deferred
     * insert never touches the request object after termination.
     *
     * @return array<string, mixed>
     */
    public function payload(Request $request): array
    {
        return [
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
            'page_visited' => Str::limit($request->path(), 255),
            'referer' => Str::limit((string) $request->headers->get('referer'), 255) ?: null,
            'device_type' => app(DeviceDetector::class)->detect((string) $request->userAgent()),
            'visited_at' => now(),
        ];
    }

    /**
     * Persist a previously captured visitor payload.
     *
     * Never throws: tracking must never break the request cycle.
     *
     * @param  array<string, mixed>  $payload
     */
    public function recordPayload(array $payload): bool
    {
        try {
            Visitor::query()->create($payload);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Total number of recorded visits.
     */
    public function total(): int
    {
        return Visitor::query()->count();
    }

    /**
     * Number of visits recorded on the given date (or today by default).
     */
    public function countForDay(string $date = 'today'): int
    {
        return Visitor::query()
            ->whereDate('visited_at', $date)
            ->count();
    }

    /**
     * Per-day visit counts over the last N days (most recent first).
     *
     * @return array<int, array{date: string, count: int}>
     */
    public function dailyCounts(int $days = 30): array
    {
        return Visitor::query()
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
            ->where('visited_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => (string) $row->date,
                'count' => (int) $row->count,
            ])
            ->all();
    }

    /**
     * Visit counts grouped by device type.
     *
     * @return array<string, int>
     */
    public function countsByDevice(): array
    {
        $rows = Visitor::query()
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->pluck('count', 'device_type');

        return [
            DeviceType::Desktop->value => (int) $rows[DeviceType::Desktop->value] ?? 0,
            DeviceType::Mobile->value => (int) $rows[DeviceType::Mobile->value] ?? 0,
            DeviceType::Tablet->value => (int) $rows[DeviceType::Tablet->value] ?? 0,
        ];
    }

    /**
     * Most visited pages.
     *
     * @return array<int, array{page_visited: string, count: int}>
     */
    public function topPages(int $limit = 10): array
    {
        return Visitor::query()
            ->selectRaw('page_visited, COUNT(*) as count')
            ->groupBy('page_visited')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'page_visited' => $row->page_visited,
                'count' => (int) $row->count,
            ])
            ->all();
    }

    /**
     * Delete visitor records older than the given number of days.
     *
     * @return int number of deleted rows
     */
    public function prune(int $days): int
    {
        $cutoff = now()->subDays($days);

        return Visitor::query()
            ->where('visited_at', '<', $cutoff)
            ->delete();
    }
}
