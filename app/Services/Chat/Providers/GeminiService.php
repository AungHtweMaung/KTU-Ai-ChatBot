<?php

namespace App\Services\Chat\Providers;

use App\Services\Chat\Exceptions\ChatException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini (generateContent) implementation of AiProvider.
 *
 * Gemini's message shape is `contents: [{ role, parts:[{text}] }]` — this
 * class translates from the OpenAI-style array the abstract layer produces
 * into Gemini's shape, and merges any `system` message into the request's
 * dedicated `systemInstruction` field.
 */
class GeminiService extends AbstractAiProvider
{
    public function name(): string
    {
        return 'gemini';
    }

    protected function chat(array $messages, bool $jsonMode = false): string
    {
        [$systemInstruction, $contents] = $this->splitSystem($messages);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $jsonMode ? 0.0 : 0.4,
            ],
        ];

        if ($systemInstruction !== null) {
            $payload['systemInstruction'] = ['parts' => [['text' => $systemInstruction]]];
        }

        if ($jsonMode) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent";

        try {
            $response = Http::withQueryParameters(['key' => $this->apiKey])
                ->timeout(config('chat.timeout'))
                ->connectTimeout(config('chat.connect_timeout'))
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::warning('Gemini connection failure', ['message' => $e->getMessage()]);
            throw ChatException::providerFailed('gemini', 'Please try again in a moment.');
        }

        if ($response->failed()) {
            Log::warning('Gemini request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw ChatException::providerFailed('gemini', 'Please try again in a moment.');
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $text = collect($parts)->pluck('text')->filter()->implode('');

        if ($text === '') {
            throw ChatException::providerFailed('gemini', 'Empty response.');
        }

        return $text;
    }

    /**
     * Separate the (single) system instruction from the conversation and
     * convert message roles into Gemini's vocabulary
     * (assistant → model, user → user).
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{0: ?string, 1: array<int, array{role: string, parts: array<int, array{text: string}>}>}
     */
    private function splitSystem(array $messages): array
    {
        $system = null;
        $contents = [];

        foreach ($messages as $m) {
            $role = $m['role'];
            $content = (string) $m['content'];

            if ($role === 'system') {
                $system = $system === null ? $content : $system."\n\n".$content;
                continue;
            }

            $contents[] = [
                'role' => $role === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $content]],
            ];
        }

        return [$system, $contents];
    }
}
