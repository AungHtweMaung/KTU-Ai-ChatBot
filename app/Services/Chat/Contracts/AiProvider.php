<?php

namespace App\Services\Chat\Contracts;

/**
 * A generic AI provider capable of understanding a user's intent and
 * generating natural-language answers from structured data.
 *
 * Implementations must NOT touch the database, execute SQL, or talk to any
 * university-specific services — they only translate between plain text and
 * structured JSON, and vice versa.
 */
interface AiProvider
{
    /**
     * Phase 1: given the recent conversation history and the latest user
     * message, return a structured intent envelope.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{intent: string, confidence: float, filters: array<string, mixed>, follow_up?: string}
     */
    public function extractIntent(array $history, string $latestMessage): array;

    /**
     * Phase 3: given the original user question and the database result
     * assembled by ChatService, produce a friendly natural-language answer.
     *
     * @param  array<string, mixed>|list<array<string, mixed>>  $dbResult
     */
    public function generateAnswer(string $userQuestion, string $intent, array $dbResult): string;

    /**
     * Identifier for diagnostics (e.g. "openai", "gemini").
     */
    public function name(): string;
}
