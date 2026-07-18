<?php

namespace App\Services\Chat\Intents;

use App\Models\Event;
use App\Services\Chat\Contracts\IntentHandler;
use Illuminate\Support\Carbon;

/**
 * Upcoming events, seminars, holidays. Supports keyword and coarse date
 * filters ("today", "this week", ISO dates).
 */
class EventSearchHandler implements IntentHandler
{
    public function name(): string
    {
        return 'event_search';
    }

    public function handle(array $filters): array
    {
        $keyword = trim((string) ($filters['query'] ?? ''));
        $date = trim((string) ($filters['event_date'] ?? ''));

        $query = Event::query()
            ->where('is_published', true)
            ->orderBy('starts_at');

        if ($keyword !== '') {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('type', 'like', "%{$keyword}%"));
        }

        [$from, $to] = $this->resolveDateWindow($date);
        if ($from) {
            $query->where('starts_at', '>=', $from);
        }
        if ($to) {
            $query->where('starts_at', '<=', $to);
        }

        return $query->limit(15)->get()->map(fn (Event $e) => [
            'title' => $e->title,
            'type' => $e->type,
            'location' => $e->location,
            'starts_at' => optional($e->starts_at)->toDateTimeString(),
            'ends_at' => optional($e->ends_at)->toDateTimeString(),
            'description' => $e->description,
        ])->all();
    }

    /**
     * Convert a natural date filter into a [from, to] Carbon window.
     * Unknown/empty values default to "from now on" — a common ask.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveDateWindow(string $date): array
    {
        $now = Carbon::now();

        return match (strtolower($date)) {
            '', 'upcoming', 'future' => [$now, null],
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'tomorrow' => [$now->copy()->addDay()->startOfDay(), $now->copy()->addDay()->endOfDay()],
            'this week', 'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this month', 'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => $this->tryIso($date),
        };
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    private function tryIso(string $date): array
    {
        try {
            $d = Carbon::parse($date);

            return [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
        } catch (\Throwable) {
            return [Carbon::now(), null];
        }
    }
}
