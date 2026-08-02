<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\AiDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiAgentController – demonstrates the basic Agent pattern.
 *
 * Teaching point: Agent = reusable AI capability class.
 */
class AiAgentController extends Controller
{
    public function __construct(private readonly AiDemoService $service) {}

    public function show(): View
    {
        return view('demo.ai.agent');
    }

    public function prompt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
        ]);

        $start = hrtime(true);

        try {
            $response = $this->service->promptAgent($validated['prompt']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'AI request failed: '.$e->getMessage(),
            ], 422);
        }

        $elapsedMs = (int) ((hrtime(true) - $start) / 1_000_000);

        return response()->json([
            'text' => $response->text,
            'model' => $response->meta->model ?? 'gemini',
            'elapsed_ms' => $elapsedMs,
        ]);
    }
}
