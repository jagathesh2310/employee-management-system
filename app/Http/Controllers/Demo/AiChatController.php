<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\AiDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiChatController – demonstrates multi-turn persistent conversations.
 *
 * Teaching point: RemembersConversations stores history per user for follow-up context.
 */
class AiChatController extends Controller
{
    public function __construct(private readonly AiDemoService $service) {}

    public function show(): View
    {
        return view('demo.ai.chat');
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'max:50'],
        ]);

        $start = hrtime(true);

        try {
            $response = $this->service->chatMessage(
                $validated['message'],
                $request->user(),
                $validated['conversation_id'] ?? null,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'AI request failed: '.$e->getMessage(),
            ], 422);
        }

        $elapsedMs = (int) ((hrtime(true) - $start) / 1_000_000);

        return response()->json([
            'text' => $response->text,
            'conversation_id' => $response->conversationId,
            'elapsed_ms' => $elapsedMs,
        ]);
    }

    public function reset(): JsonResponse
    {
        return response()->json(['conversation_id' => null]);
    }
}
