<?php

namespace App\Services\Chat\Intents;

use App\Services\Chat\Contracts\IntentHandler;

/**
 * Placeholder handler — registration schedule/period data is not modelled
 * yet. Empty result => the answer layer will apologise and offer to help
 * with related topics (registration fees, etc.).
 */
class RegistrationScheduleHandler implements IntentHandler
{
    public function name(): string
    {
        return 'registration_schedule';
    }

    public function handle(array $filters): array
    {
        // TODO: wire up when a RegistrationPeriod model is added.
        return [];
    }
}
