<?php

namespace App\Services\Chat\Providers;

use App\Services\Chat\Contracts\AiProvider;
use App\Services\Chat\Exceptions\ChatException;
use App\Services\Chat\Prompts\SystemPrompts;

/**
 * Shared behaviour for concrete AI providers.
 *
 * Handles config loading, guard-rails (missing keys), JSON parsing of the
 * intent envelope, and normalisation of the intent name against the
 * whitelist in `config('chat.intents')`.
 *
 * Concrete providers implement the transport-level methods
 * (`callChatCompletion`, `callTextGeneration`) that talk to their vendor.
 */
abstract class AbstractAiProvider implements AiProvider
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $config = config("chat.providers.{$this->name()}");

        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $this->model = (string) ($config['model'] ?? '');

        if ($this->apiKey === '') {
            throw ChatException::providerNotConfigured($this->name());
        }
    }

    /**
     * Talk to the provider's chat-completion / text-generation endpoint.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     *   The `system` message is always the first element; the rest are the
     *   sliding-window conversation.
     * @param  bool  $jsonMode  If true, ask the model to emit strict JSON.
     */
    abstract protected function chat(array $messages, bool $jsonMode = false): string;

    /* -----------------------------------------------------------------
     |  AiProvider — Phase 1
     |----------------------------------------------------------------- */

    public function extractIntent(array $history, string $latestMessage): array
    {
        $messages = array_merge(
            [['role' => 'system', 'content' => SystemPrompts::intent()]],
            $history,
            [['role' => 'user', 'content' => $latestMessage]],
        );

        $raw = $this->chat($messages, jsonMode: true);

        return $this->parseIntentEnvelope($raw);
    }

    /* -----------------------------------------------------------------
     |  AiProvider — Phase 3
     |----------------------------------------------------------------- */

    public function generateAnswer(string $userQuestion, string $intent, array $dbResult): string
    {
        $payload = json_encode(
            ['user_question' => $userQuestion, 'intent' => $intent, 'data' => $dbResult],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        $messages = [
            ['role' => 'system', 'content' => SystemPrompts::answer()],
            ['role' => 'user', 'content' => $payload],
        ];

        return trim($this->chat($messages));
    }

    /* -----------------------------------------------------------------
     |  Shared helpers
     |----------------------------------------------------------------- */

    /**
     * Parse the JSON envelope the AI returns during intent extraction.
     * Falls back to `clarification_required` on malformed output.
     */
    protected function parseIntentEnvelope(string $raw): array
    {
        // The AI sometimes wraps JSON in ```json fences — strip those.
        $stripped = trim(preg_replace('/^```(?:json)?|```$/mi', '', trim($raw)));

        $decoded = json_decode($stripped, true);

        if (! is_array($decoded) || ! isset($decoded['intent'])) {
            return $this->fallbackIntent();
        }

        $intent = (string) $decoded['intent'];
        if (! in_array($intent, config('chat.intents'), true)) {
            $intent = 'clarification_required';
        }

        return [
            'intent' => $intent,
            'confidence' => (float) ($decoded['confidence'] ?? 0.5),
            'filters' => is_array($decoded['filters'] ?? null) ? $decoded['filters'] : [],
            'follow_up' => isset($decoded['follow_up']) ? (string) $decoded['follow_up'] : null,
        ];
    }

    protected function fallbackIntent(): array
    {
        return [
            'intent' => 'clarification_required',
            'confidence' => 0.0,
            'filters' => [],
            'follow_up' => 'Could you rephrase your question? I want to make sure I understand what you need.',
        ];
    }
}
