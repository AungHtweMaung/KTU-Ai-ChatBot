<?php

namespace App\Services\Chat\Intents;

use App\Models\Faq;
use App\Services\Chat\Contracts\IntentHandler;

/**
 * Frequently-asked-questions search across question, answer, and category.
 */
class FaqSearchHandler implements IntentHandler
{
    public function name(): string
    {
        return 'faq_search';
    }

    public function handle(array $filters): array
    {
        $query = trim((string) ($filters['query'] ?? ''));

        $builder = Faq::query()
            ->where('is_published', true)
            ->orderBy('sort_order');

        if ($query !== '') {
            $builder->where(fn ($q) => $q
                ->where('question', 'like', "%{$query}%")
                ->orWhere('answer', 'like', "%{$query}%")
                ->orWhere('category', 'like', "%{$query}%"));
        }

        $total = (clone $builder)->count();

        $items = $builder->limit(10)->get()->map(fn (Faq $f) => [
            'category' => $f->category,
            'question' => $f->question,
            'answer' => $f->answer,
        ])->all();

        return [
            'total_count' => $total,
            'items' => $items,
        ];
    }
}
