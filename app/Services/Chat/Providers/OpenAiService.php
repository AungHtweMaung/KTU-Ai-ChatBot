<?php

namespace App\Services\Chat\Providers;

use App\Services\Chat\Exceptions\ChatException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI (chat/completions) implementation of AiProvider.
 *
 * Uses the JSON-mode ("response_format" => "json_object") feature during
 * intent extraction so the model reliably returns parseable JSON.
 */
class OpenAiService extends AbstractAiProvider
{
    public function name(): string
    {
        return 'openai';
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
            Log::warning('OpenAI connection failure', ['message' => $e->getMessage()]);
            throw ChatException::providerFailed('openai', 'Please try again in a moment.');
        }

        if ($response->failed()) {
            Log::warning('OpenAI request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw ChatException::providerFailed('openai', 'Please try again in a moment.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || $content === '') {
            throw ChatException::providerFailed('openai', 'Empty response.');
        }

        return $content;
    }
}
