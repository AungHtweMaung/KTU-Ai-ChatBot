<?php

namespace App\Services\Chat\Intents;

use App\Models\RegistrationFee;
use App\Services\Chat\Contracts\IntentHandler;

/**
 * Registration / tuition / lab / hostel fees. Supports filtering by major
 * name or fee type; universal fees (major_id NULL) are always included.
 */
class RegistrationFeeHandler implements IntentHandler
{
    public function name(): string
    {
        return 'registration_fee';
    }

    public function handle(array $filters): array
    {
        $major = trim((string) ($filters['major'] ?? ''));
        $feeType = trim((string) ($filters['fee_type'] ?? $filters['registration_type'] ?? ''));

        $query = RegistrationFee::query()->with('major:id,name');

        if ($major !== '') {
            $query->where(function ($q) use ($major) {
                $q->whereNull('major_id')
                    ->orWhereHas('major', fn ($m) => $m->where('name', 'like', "%{$major}%"));
            });
        }

        if ($feeType !== '') {
            $query->where('name', 'like', "%{$feeType}%");
        }

        $total = (clone $query)->count();

        $items = $query->limit(20)->get()->map(fn (RegistrationFee $f) => [
            'name' => $f->name,
            'major' => $f->major?->name ?? 'All majors',
            'amount' => (float) $f->amount,
            'currency' => 'MMK',
            'description' => $f->description,
        ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
