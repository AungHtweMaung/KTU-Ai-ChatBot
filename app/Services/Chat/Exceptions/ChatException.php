<?php

namespace App\Services\Chat\Exceptions;

use RuntimeException;

/**
 * Domain-specific exception the ChatService throws for any recoverable
 * chat-pipeline failure (provider timeout, malformed JSON, missing API key,
 * unavailable upstream, etc.).
 *
 * The controller renders the ->getMessage() as a user-friendly error.
 */
class ChatException extends RuntimeException
{
    public static function providerNotConfigured(string $provider): self
    {
        return new self("The AI provider [{$provider}] is not configured. Please add its API key to .env.");
    }

    public static function providerFailed(string $provider, string $reason): self
    {
        return new self("The AI service ({$provider}) is temporarily unavailable. {$reason}");
    }

    public static function invalidIntentJson(string $raw): self
    {
        return new self('The AI returned an invalid response. Please try again.');
    }
}
