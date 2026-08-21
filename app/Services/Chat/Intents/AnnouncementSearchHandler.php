<?php

namespace App\Services\Chat\Intents;

use App\Services\Chat\Contracts\IntentHandler;

/**
 * Placeholder handler — the Announcement module has not been implemented yet.
 * Returning an empty array causes the answer layer to politely tell the user
 * no matching information was found.
 *
 * When you add the model, replace this file's `handle()` body with an
 * Eloquent query and everything else keeps working unchanged.
 */
class AnnouncementSearchHandler implements IntentHandler
{
    public function name(): string
    {
        return 'announcement_search';
    }

    public function handle(array $filters): array
    {
        // TODO: wire up once the Announcement model exists.
        return [];
    }
}
