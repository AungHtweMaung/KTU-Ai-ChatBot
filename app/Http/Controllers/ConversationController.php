<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * List / view / rename / delete conversation threads.
 *
 * Ownership is enforced consistently for both authenticated users
 * (matched by user_id) and anonymous visitors (matched by guest_uuid
 * — the client sends this along in the query string / payload).
 */
class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conversations = $this->scopedQuery($request)
            ->latest('updated_at')
            ->limit(200)
            ->get(['id', 'title', 'created_at', 'updated_at'])
            ->map(fn (Conversation $c) => [
                'id' => $c->id,
                'title' => $c->title ?: 'New chat',
                'updated_at' => $c->updated_at->toIso8601String(),
            ]);

        return response()->json(['data' => $conversations]);
    }

    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeAccess($request, $conversation);

        $messages = $conversation->messages()
            ->where('role', '!=', 'system')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'role' => $m->role === 'assistant' ? 'ai' : 'user',
                'content' => $m->content,
                'createdAt' => $m->created_at->toIso8601String(),
            ]);

        return response()->json([
            'id' => $conversation->id,
            'title' => $conversation->title,
            'messages' => $messages,
        ]);
    }

    public function update(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeAccess($request, $conversation);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
        ]);

        $conversation->update(['title' => $data['title']]);

        return response()->json([
            'id' => $conversation->id,
            'title' => $conversation->title,
        ]);
    }

    public function destroy(Request $request, Conversation $conversation): Response
    {
        $this->authorizeAccess($request, $conversation);

        $conversation->delete();

        return response()->noContent();
    }

    /* -----------------------------------------------------------------
     |  Helpers
     |----------------------------------------------------------------- */

    private function scopedQuery(Request $request)
    {
        $user = $request->user();
        if ($user) {
            return Conversation::where('user_id', $user->id);
        }

        $uuid = (string) $request->query('guest_uuid', '');
        if ($uuid === '') {
            // No auth + no guest UUID = no history (fresh browser).
            return Conversation::whereRaw('0 = 1');
        }

        return Conversation::where('guest_uuid', $uuid);
    }

    private function authorizeAccess(Request $request, Conversation $conversation): void
    {
        $user = $request->user();

        $ok = $user
            ? $conversation->user_id === $user->id
            : $conversation->guest_uuid === (string) ($request->input('guest_uuid') ?? $request->query('guest_uuid'));

        abort_unless($ok, 403);
    }
}
