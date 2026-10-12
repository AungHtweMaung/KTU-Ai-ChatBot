<?php

namespace App\Services\Chat\Intents;

use App\Models\Timetable;
use App\Services\Chat\Contracts\IntentHandler;
use App\Services\Chat\Intents\Concerns\LooseMatching;

/**
 * "Show me the timetable for third year CEIT" / "class schedule" — returns the
 * uploaded timetable image(s) for a class, optionally narrowed by major, year,
 * and semester.
 */
class TimetableSearchHandler implements IntentHandler
{
    use LooseMatching;

    public function name(): string
    {
        return 'timetable_search';
    }

    public function handle(array $filters): array
    {
        $major = trim((string) ($filters['major'] ?? ''));
        $department = trim((string) ($filters['department'] ?? ''));
        $majorYear = trim((string) ($filters['major_year'] ?? ''));
        $semester = trim((string) ($filters['semester'] ?? ''));

        $query = Timetable::query()->with('majorYear.major.department');

        if ($major !== '' || $department !== '') {
            $query->whereHas('majorYear.major', function ($m) use ($major, $department) {
                if ($major !== '') {
                    $this->looseLike($m, ['name'], $major);
                }
                if ($department !== '') {
                    $m->whereHas('department', fn ($d) => $this->looseLike($d, ['name', 'code'], $department));
                }
            });
        }

        if ($majorYear !== '') {
            $query->whereHas('majorYear', function ($y) use ($majorYear) {
                if (ctype_digit($majorYear)) {
                    $y->where('year_number', (int) $majorYear);
                } else {
                    $this->looseLike($y, ['name'], $majorYear);
                }
            });
        }

        if ($semester !== '' && ctype_digit($semester)) {
            $query->where('semester', (int) $semester);
        }

        $total = (clone $query)->count();

        $items = $query
            ->limit(25)
            ->get()
            ->map(fn (Timetable $t) => [
                'class' => $t->majorYear?->name,
                'major' => $t->majorYear?->major?->name,
                'department' => $t->majorYear?->major?->department?->name,
                'semester' => $t->semester,
                'image_url' => $t->image_url,
            ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
