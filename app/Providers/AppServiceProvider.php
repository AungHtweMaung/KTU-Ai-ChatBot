<?php

namespace App\Providers;

use App\Services\Chat\Contracts\AiProvider;
use App\Services\Chat\Exceptions\ChatException;
use App\Services\Chat\Providers\GeminiService;
use App\Services\Chat\Providers\OpenAiService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the AiProvider interface to whichever concrete implementation
        // is selected in `config/chat.php` — swap providers with a single env
        // variable, no code changes required.
        $this->app->singleton(AiProvider::class, function () {
            return match (config('chat.provider')) {
                'openai' => new OpenAiService(),
                'gemini' => new GeminiService(),
                default => throw ChatException::providerNotConfigured((string) config('chat.provider')),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
