<?php

namespace App\Providers;

use App\Services\Chat\IntentRegistry;
use App\Services\Chat\Intents\AnnouncementSearchHandler;
use App\Services\Chat\Intents\DepartmentInformationHandler;
use App\Services\Chat\Intents\EventSearchHandler;
use App\Services\Chat\Intents\FaqSearchHandler;
use App\Services\Chat\Intents\MajorInformationHandler;
use App\Services\Chat\Intents\RegistrationFeeHandler;
use App\Services\Chat\Intents\RegistrationScheduleHandler;
use App\Services\Chat\Intents\SubjectSearchHandler;
use App\Services\Chat\Intents\TeacherProfileHandler;
use App\Services\Chat\Intents\TeacherSearchHandler;
use Illuminate\Support\ServiceProvider;

/**
 * Wires up the chat-pipeline components:
 *  - the IntentRegistry (singleton),
 *  - one IntentHandler per supported intent.
 *
 * To add a new intent: implement IntentHandler and append it here.
 */
class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IntentRegistry::class, function () {
            return (new IntentRegistry())
                ->register(new TeacherSearchHandler())
                ->register(new TeacherProfileHandler())
                ->register(new SubjectSearchHandler())
                ->register(new MajorInformationHandler())
                ->register(new DepartmentInformationHandler())
                ->register(new RegistrationFeeHandler())
                ->register(new RegistrationScheduleHandler())
                ->register(new AnnouncementSearchHandler())
                ->register(new EventSearchHandler())
                ->register(new FaqSearchHandler());
        });
    }
}
