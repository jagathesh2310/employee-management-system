<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\AiDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiToolsController – demonstrates agents with tool calls.
 *
 * Teaching point: Tools let agents query real data from your app.
 */
class AiToolsController extends Controller
{
    public function __construct(private readonly AiDemoService $service) {}

    public function show(): View
    {
        return view('demo.ai.tools');
    }

    public function prompt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
        ]);

        $start = hrtime(true);

        try {
            $response = $this->service->promptWithTools($validated['prompt']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'AI request failed: '.$e->getMessage(),
            ], 422);
        }

        $elapsedMs = (int) ((hrtime(true) - $start) / 1_000_000);

        $toolCalls = $response->toolCalls->map(fn ($tc) => [
            'name' => $tc->name,
            'arguments' => $tc->arguments,
        ])->values()->toArray();

        $toolResults = $response->toolResults->map(fn ($tr) => [
            'name' => $tr->name,
            'result' => is_string($tr->result) ? $tr->result : json_encode($tr->result),
        ])->values()->toArray();

        return response()->json([
            'text' => $response->text,
            'tool_calls' => $toolCalls,
            'tool_results' => $toolResults,
            'model' => $response->meta->model ?? 'gemini',
            'elapsed_ms' => $elapsedMs,
        ]);
    }
}
