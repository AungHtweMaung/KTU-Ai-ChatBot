<?php

namespace App\Services\Chat;

use App\Services\Chat\Contracts\IntentHandler;

/**
 * Runtime registry mapping intent names → their IntentHandler.
 *
 * The registry itself is a singleton; handlers are registered inside
 * ChatServiceProvider so a new intent can be added by simply implementing
 * IntentHandler and appending a `->register(new XxxHandler)` call.
 */
class IntentRegistry
{
    /** @var array<string, IntentHandler> */
    private array $handlers = [];

    public function register(IntentHandler $handler): self
    {
        $this->handlers[$handler->name()] = $handler;

        return $this;
    }

    public function has(string $intent): bool
    {
        return isset($this->handlers[$intent]);
    }

    /**
     * Dispatch to the handler for the given intent, or return an empty
     * result if no handler is registered (e.g. greeting, general_chat,
     * clarification_required).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function dispatch(string $intent, array $filters): array
    {
        return $this->has($intent)
            ? $this->handlers[$intent]->handle($filters)
            : [];
    }
}
