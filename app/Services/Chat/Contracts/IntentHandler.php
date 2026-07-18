<?php

namespace App\Services\Chat\Contracts;

/**
 * A single intent's business logic. Each handler owns the Eloquent queries
 * needed to satisfy one AI-detected intent (e.g. teacher_search, event_search)
 * and returns a plain array that will be forwarded to the AI for answer
 * generation.
 *
 * Handlers must:
 *  - never inspect anything outside their own filters,
 *  - never call the AI,
 *  - never emit HTML/Markdown — data only.
 */
interface IntentHandler
{
    /**
     * The intent name this handler serves (must match one entry in
     * `config('chat.intents')`).
     */
    public function name(): string;

    /**
     * Execute the intent using the AI-supplied business filters.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function handle(array $filters): array;
}
