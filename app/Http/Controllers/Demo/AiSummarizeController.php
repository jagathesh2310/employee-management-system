<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\AiDemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AiSummarizeController – text summarization demo.
 *
 * Teaching point: Focused agents for single-purpose tasks.
 */
class AiSummarizeController extends Controller
{
    public function __construct(private readonly AiDemoService $service) {}

    public function show(): View
    {
        return view('demo.ai.summarize');
    }

    public function summarize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'min:50', 'max:5000'],
        ]);

        $start = hrtime(true);

        try {
            $response = $this->service->summarize($validated['text']);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Summarization failed: '.$e->getMessage(),
            ], 422);
        }

        $elapsedMs = (int) ((hrtime(true) - $start) / 1_000_000);

        return response()->json([
            'summary' => $response->text,
            'original_length' => strlen($validated['text']),
            'summary_length' => strlen($response->text),
            'elapsed_ms' => $elapsedMs,
        ]);
    }
}
