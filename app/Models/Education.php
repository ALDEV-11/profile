<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'institution',
    'field_of_study',
    'degree',
    'start_date',
    'end_date',
    'is_currently_studying',
    'order',
])]
class Education extends Model
{
    protected $table = 'educations';

    /** @use HasFactory<EducationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_currently_studying' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format the education period as a displayable date range.
     *
     * @return string e.g. "2021 - 2024" or "2021 - Present"
     */
    public function dateRange(): string
    {
        $start = $this->start_date?->format('Y');

        if ($this->is_currently_studying) {
            return $start ? "{$start} - Present" : 'Present';
        }

        $end = $this->end_date?->format('Y');

        if ($start && $end) {
            return "{$start} - {$end}";
        }

        return $start ?? $end ?? '—';
    }

    /**
     * Detect the education level used for color-coding the timeline.
     */
    public function degreeLevel(): string
    {
        $d = strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', $this->degree));

        if (Str::contains($d, ['smk', 'sma'])) {
            return 'smk';
        }

        if (Str::contains($d, ['s2', 'master', 'magister', 'msc', 'meng', 'mba'])) {
            return 'master';
        }

        if (Str::contains($d, ['s1', 'bachelor', 'sarjana', 'bsc', 'beng'])) {
            return 'bachelor';
        }

        if (Str::contains($d, 'd4')) {
            return 'd4';
        }

        if (Str::contains($d, ['d3', 'diploma'])) {
            return 'd3';
        }

        return 'other';
    }

    /**
     * Short label for the degree badge (e.g. SMK, S1, S2, D3).
     */
    public function degreeShort(): string
    {
        return match ($this->degreeLevel()) {
            'smk' => 'SMK',
            'bachelor' => 'S1',
            'master' => 'S2',
            'd3' => 'D3',
            'd4' => 'D4',
            default => mb_strtoupper(Str::limit($this->degree ?? 'EDU', 4, '')),
        };
    }

    /**
     * Boxicon class for the timeline node, based on the education level.
     */
    public function degreeIcon(): string
    {
        return match ($this->degreeLevel()) {
            'smk' => 'bxs-school',
            'bachelor' => 'bxs-graduation',
            'master' => 'bxs-award',
            default => 'bx-book-open',
        };
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('start_date')->orderBy('order');
    }
}
