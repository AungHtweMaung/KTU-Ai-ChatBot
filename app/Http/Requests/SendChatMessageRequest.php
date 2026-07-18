<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for POST /chat/send.
 *
 * Both `conversation_id` and `guest_uuid` are optional — the ChatService
 * resolves the caller from `$request->user()` first, falling back to the
 * guest UUID so anonymous browsers can hold a conversation too.
 */
class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:'.(int) config('chat.max_message_length', 2000)],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'guest_uuid' => ['nullable', 'uuid'],
        ];
    }
}
