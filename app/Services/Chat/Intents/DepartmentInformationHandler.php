<?php

namespace App\Services\Chat\Intents;

use App\Models\Department;
use App\Services\Chat\Contracts\IntentHandler;

/**
 * Information about a department (by name or code) — includes majors.
 */
class DepartmentInformationHandler implements IntentHandler
{
    public function name(): string
    {
        return 'department_information';
    }

    public function handle(array $filters): array
    {
        $department = trim((string) ($filters['department'] ?? ''));

        $query = Department::query();

        if ($department !== '') {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$department}%")
                ->orWhere('code', 'like', "%{$department}%"));
        }

        $total = (clone $query)->count();

        $items = $query
            ->with('majors:id,department_id,name')
            ->limit(10)
            ->get()
            ->map(fn (Department $d) => [
                'name' => $d->name,
                'code' => $d->code,
                'description' => $d->description,
                'majors' => $d->majors->pluck('name')->all(),
            ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
