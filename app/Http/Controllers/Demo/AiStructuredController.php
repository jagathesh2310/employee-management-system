<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\AiDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiStructuredController – demonstrates structured (schema-enforced) output.
 *
 * Teaching point: Structured output returns validated JSON fields, not free text.
 */
class AiStructuredController extends Controller
{
    public function __construct(private readonly AiDemoService $service) {}

    public function show(): View
    {
        return view('demo.ai.structured');
    }

    public function prompt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:2000'],
        ]);

        $start = hrtime(true);

        try {
            $response = $this->service->promptStructured($validated['prompt']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'AI request failed: '.$e->getMessage(),
            ], 422);
        }

        $elapsedMs = (int) ((hrtime(true) - $start) / 1_000_000);
        $structured = $response->toArray();

        return response()->json([
            'structured' => $structured,
            'raw_text' => $response->text,
            'model' => $response->meta->model ?? 'gemini',
            'elapsed_ms' => $elapsedMs,
        ]);
    }
}
