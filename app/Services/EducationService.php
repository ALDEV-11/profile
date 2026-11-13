<?php

namespace App\Services;

use App\Models\Education;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EducationService
{
    /**
     * List the user's educations, newest first (10 per page).
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(int $userId, array $filters = []): LengthAwarePaginator
    {
        return Education::query()
            ->where('user_id', $userId)
            ->when($filters['q'] ?? null, function (Builder $query, string $q) {
                $query->where(function (Builder $inner) use ($q) {
                    $inner->whereLike('institution', "%{$q}%", caseSensitive: false)
                        ->orWhereLike('field_of_study', "%{$q}%", caseSensitive: false)
                        ->orWhereLike('degree', "%{$q}%", caseSensitive: false);
                });
            })
            ->orderByDesc('start_date')
            ->orderBy('order')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Persist a new education record for the user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $userId, array $data): Education
    {
        $data['user_id'] = $userId;
        $data['order'] = $data['order'] ?? $this->nextOrder($userId);

        return Education::create($this->normalizeDates($data));
    }

    /**
     * Update an existing education record.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Education $education, array $data): Education
    {
        $education->update($this->normalizeDates($data));

        return $education;
    }

    /**
     * Convert year-only inputs (e.g. "2018") to a date (2018-01-01).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeDates(array $data): array
    {
        foreach (['start_date', 'end_date'] as $field) {
            if (isset($data[$field]) && is_numeric($data[$field])) {
                $data[$field] = $data[$field].'-01-01';
            }
        }

        return $data;
    }

    /**
     * Soft delete an education record.
     */
    public function delete(Education $education): void
    {
        $education->delete();
    }

    /**
     * Restore a soft-deleted education record.
     */
    public function restore(Education $education): void
    {
        $education->restore();
    }

    /**
     * Next display order value for the user.
     */
    public function nextOrder(int $userId): int
    {
        return (int) Education::query()
            ->where('user_id', $userId)
            ->max('order') + 1;
    }
}
