<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Chat\Contracts\AiProvider;
use App\Services\Chat\Exceptions\ChatException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * The chat pipeline orchestrator. Owns the entire flow between the
 * controller and the AI + database — this is where "business logic" lives.
 *
 * Flow for `send`:
 *   1. Resolve (or create) the conversation for the caller.
 *   2. Load the sliding-window history from the DB (config-driven size).
 *   3. Ask the AI (Phase 1) to extract intent + filters.
 *   4. Dispatch the intent to its IntentHandler → get structured data.
 *   5. Ask the AI (Phase 3) to phrase the answer using ONLY that data.
 *   6. Persist the user and assistant messages, auto-title if new.
 *   7. Return the payload the controller renders to JSON.
 */
class ChatService
{
    public function __construct(
        private readonly AiProvider $ai,
        private readonly IntentRegistry $intents,
    ) {
    }

    /**
     * Public entry point called by ChatController.
     *
     * @param  array{message: string, conversation_id?: ?int, guest_uuid?: ?string}  $input
     * @return array{
     *   conversation_id: int,
     *   guest_uuid: ?string,
     *   title: ?string,
     *   assistant_message: array{id: int, role: string, content: string, created_at: string},
     *   intent: string
     * }
     */
    public function send(array $input, ?User $user): array
    {
        // Look up an existing conversation but do NOT create one yet — a
        // fresh row is only persisted below, inside the transaction that
        // also stores the turn's messages. That way, if the AI call fails
        // (rate limit, network, etc.) the DB stays clean and the sidebar
        // isn't polluted with empty "New chat" rows.
        $conversation = $this->findExistingConversation(
            $input['conversation_id'] ?? null,
            $user,
            $input['guest_uuid'] ?? null,
        );

        try {
            $history = $conversation ? $this->loadSlidingWindow($conversation) : [];
            $envelope = $this->ai->extractIntent($history, $input['message']);

            $data = $this->intents->dispatch($envelope['intent'], $envelope['filters']);

            $answer = $this->ai->generateAnswer(
                $input['message'],
                $envelope['intent'],
                is_array($data) ? $data : [],
            );

            return DB::transaction(function () use ($conversation, $input, $envelope, $answer, $user) {
                // Create the conversation lazily on the first successful turn.
                $conversation ??= Conversation::create([
                    'user_id' => $user?->id,
                    'guest_uuid' => $user
                        ? null
                        : ($input['guest_uuid'] ?? (string) Str::uuid()),
                ]);

                Message::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'user',
                    'content' => $input['message'],
                ]);

                $assistant = Message::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => $answer,
                    'meta' => [
                        'intent' => $envelope['intent'],
                        'confidence' => $envelope['confidence'],
                        'provider' => $this->ai->name(),
                    ],
                ]);

                if (! $conversation->title) {
                    $conversation->title = $this->titleFrom($input['message']);
                }
                $conversation->touch();
                $conversation->save();

                return [
                    'conversation_id' => $conversation->id,
                    'guest_uuid' => $conversation->guest_uuid,
                    'title' => $conversation->title,
                    'assistant_message' => [
                        'id' => $assistant->id,
                        'role' => 'assistant',
                        'content' => $assistant->content,
                        'created_at' => $assistant->created_at->toIso8601String(),
                    ],
                    'intent' => $envelope['intent'],
                ];
            });
        } catch (ChatException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Chat pipeline failure', [
                'conversation_id' => $conversation?->id,
                'error' => $e->getMessage(),
            ]);
            throw new ChatException('Something went wrong while processing your message. Please try again.');
        }
    }

    /* -----------------------------------------------------------------
     |  Helpers
     |----------------------------------------------------------------- */

    /**
     * Look up an existing conversation (validating ownership) or create a new
     * one bound either to the auth'd user or the guest UUID.
     */
    /**
     * Look up an existing conversation that belongs to the current caller.
     * Returns null when there is no valid match; the send() method will then
     * create one lazily (inside its DB transaction) once the AI call succeeds.
     */
    private function findExistingConversation(?int $id, ?User $user, ?string $guestUuid): ?Conversation
    {
        if (! $id) {
            return null;
        }

        $conversation = Conversation::find($id);
        if (! $conversation) {
            return null;
        }

        $ownedByUser = $user && $conversation->user_id === $user->id;
        $ownedByGuest = ! $user && $guestUuid && $conversation->guest_uuid === $guestUuid;

        return ($ownedByUser || $ownedByGuest) ? $conversation : null;
    }

    /**
     * Load the last N messages (config('chat.history_window')) and shape them
     * into the [{role, content}] array the AI provider consumes.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function loadSlidingWindow(Conversation $conversation): array
    {
        $limit = max(1, (int) config('chat.history_window'));

        $messages = $conversation->messages()
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $messages->map(fn (Message $m) => [
            // OpenAI / Gemini both understand "user" and "assistant".
            'role' => $m->role === 'assistant' ? 'assistant' : 'user',
            'content' => $m->content,
        ])->all();
    }

    /**
     * Derive a short conversation title from the first user message.
     */
    private function titleFrom(string $message): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $message));

        return Str::limit($clean, 60, '…');
    }
}
