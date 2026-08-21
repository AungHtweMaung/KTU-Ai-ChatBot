<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendChatMessageRequest;
use App\Services\Chat\ChatService;
use App\Services\Chat\Exceptions\ChatException;
use Illuminate\Http\JsonResponse;

/**
 * Thin HTTP boundary between the client and ChatService.
 *
 * Responsibilities: input validation (delegated to the form request),
 * calling the service, and shaping the JSON response. All business logic
 * lives inside ChatService.
 */
class ChatController extends Controller
{
    public function send(SendChatMessageRequest $request, ChatService $service): JsonResponse
    {
        try {
            $result = $service->send($request->validated(), $request->user());

            return response()->json($result);
        } catch (ChatException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}
