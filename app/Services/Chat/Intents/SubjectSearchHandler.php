<?php

namespace App\Services\Chat\Intents;

use App\Models\Subject;
use App\Services\Chat\Contracts\IntentHandler;

/**
 * "Tell me about subject X" / "List CS subjects" — matches subjects by name
 * or code.
 */
class SubjectSearchHandler implements IntentHandler
{
    public function name(): string
    {
        return 'subject_search';
    }

    public function handle(array $filters): array
    {
        $subject = trim((string) ($filters['subject'] ?? ''));

        $query = Subject::query();

        if ($subject !== '') {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$subject}%")
                ->orWhere('code', 'like', "%{$subject}%"));
        }

        return $query->limit(20)->get()->map(fn (Subject $s) => [
            'code' => $s->code,
            'name' => $s->name,
            'credits' => $s->credits,
            'description' => $s->description,
        ])->all();
    }
}
