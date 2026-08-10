<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * AiDemoHubController – renders the AI SDK Demo Hub landing page.
 */
class AiDemoHubController extends Controller
{
    public function index(): View
    {
        $mediaEnabled = (bool) env('AI_DEMO_MEDIA', false);

        $providers = config('ai.providers', []);

        $configuredProviders = collect($providers)->filter(function (array $config) {
            $keyEnvName = match ($config['driver'] ?? '') {
                'gemini' => 'GEMINI_API_KEY',
                'openai' => 'OPENAI_API_KEY',
                'anthropic' => 'ANTHROPIC_API_KEY',
                default => null,
            };

            return $keyEnvName !== null && ! empty(env($keyEnvName));
        })->keys()->values()->toArray();

        return view('demo.ai.hub', compact('mediaEnabled', 'configuredProviders'));
    }
}
