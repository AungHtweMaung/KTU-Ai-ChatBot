<?php

namespace App\Services\Chat\Intents;

use App\Models\Major;
use App\Services\Chat\Contracts\IntentHandler;
use App\Services\Chat\Intents\Concerns\LooseMatching;

/**
 * Information about a major/program — belongs to a department, has years.
 */
class MajorInformationHandler implements IntentHandler
{
    use LooseMatching;

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
            $this->looseLike($query, ['name'], $major);
        }
        if ($department !== '') {
            $query->whereHas('department', fn ($q) => $this->looseLike($q, ['name', 'code'], $department));
        }

        $total = (clone $query)->count();

        $items = $query
            ->with(['department:id,name', 'majorYears:id,major_id,year_number,name'])
            ->limit(30)
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
