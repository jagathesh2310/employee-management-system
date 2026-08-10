<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Services\AiDemoService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AiStreamController – demonstrates token-by-token streaming responses.
 *
 * Teaching point: stream() returns SSE events consumed by fetch() in the browser.
 */
class AiStreamController extends Controller
{
    public function __construct(private readonly AiDemoService $service) {}

    public function show(): View
    {
        return view('demo.ai.stream');
    }

    public function stream(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
        ]);

        $streamable = $this->service->promptStream($validated['prompt']);

        return response()->stream(function () use ($streamable) {
            foreach ($streamable as $event) {
                echo 'data: '.((string) $event)."\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
