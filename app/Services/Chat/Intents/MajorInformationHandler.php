<?php

namespace App\Services\Chat\Intents;

use App\Models\Major;
use App\Services\Chat\Contracts\IntentHandler;

/**
 * Information about a major/program — belongs to a department, has years.
 */
class MajorInformationHandler implements IntentHandler
{
    public function name(): string
    {
        return 'major_information';
    }

    public function handle(array $filters): array
    {
        $major = trim((string) ($filters['major'] ?? ''));
        $department = trim((string) ($filters['department'] ?? ''));

        // Base query (filters applied, no relations, no limit) — used for the
        // authoritative total count.
        $query = Major::query();

        if ($major !== '') {
            $query->where('name', 'like', "%{$major}%");
        }
        if ($department !== '') {
            $query->whereHas('department', fn ($q) => $q->where('name', 'like', "%{$department}%"));
        }

        $total = (clone $query)->count();

        $items = $query
            ->with(['department:id,name', 'majorYears:id,major_id,year_number,name'])
            ->limit(10)
            ->get()
            ->map(fn (Major $m) => [
                'name' => $m->name,
                'department' => $m->department?->name,
                'description' => $m->description,
                'years' => $m->majorYears
                    ->sortBy('year_number')
                    ->map(fn ($y) => ['year_number' => $y->year_number, 'name' => $y->name])
                    ->values()
                    ->all(),
            ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
