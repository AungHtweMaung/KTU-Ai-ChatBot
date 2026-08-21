<?php

namespace App\Services\Chat\Providers;

use App\Services\Chat\Exceptions\ChatException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Groq implementation of AiProvider.
 *
 * Groq exposes an OpenAI-compatible chat/completions endpoint, so the message
 * shape matches OpenAiService while credentials, base URL, and model come from
 * the groq provider config.
 */
class GroqService extends AbstractAiProvider
{
    public function name(): string
    {
        return 'groq';
    }

    protected function chat(array $messages, bool $jsonMode = false): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $jsonMode ? 0.0 : 0.4,
        ];

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(config('chat.timeout'))
                ->connectTimeout(config('chat.connect_timeout'))
                ->post("{$this->baseUrl}/chat/completions", $payload);
        } catch (ConnectionException $e) {
            Log::warning('Groq connection failure', ['message' => $e->getMessage()]);
            throw ChatException::providerFailed('groq', 'Please try again in a moment.');
        }

        if ($response->failed()) {
            Log::warning('Groq request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw ChatException::providerFailed('groq', 'Please try again in a moment.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw ChatException::providerFailed('groq', 'Empty response.');
        }

        return $content;
    }
}
