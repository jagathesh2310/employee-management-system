<?php

declare(strict_types=1);

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * AiFailoverController – shows provider configuration and failover chain.
 *
 * Teaching point: Laravel AI supports automatic provider failover.
 */
class AiFailoverController extends Controller
{
    public function show(): View
    {
        $providers = collect(config('ai.providers', []))->map(function (array $config, string $name) {
            $keyEnvName = match ($config['driver'] ?? '') {
                'gemini' => 'GEMINI_API_KEY',
                'openai' => 'OPENAI_API_KEY',
                'anthropic' => 'ANTHROPIC_API_KEY',
                'groq' => 'GROQ_API_KEY',
                'mistral' => 'MISTRAL_API_KEY',
                'xai' => 'XAI_API_KEY',
                'deepseek' => 'DEEPSEEK_API_KEY',
                default => null,
            };

            $hasKey = $keyEnvName !== null && ! empty(env($keyEnvName));

            return [
                'name' => $name,
                'driver' => $config['driver'] ?? 'unknown',
                'has_key' => $hasKey,
                'key_env' => $keyEnvName,
            ];
        })->values()->toArray();

        $default = config('ai.default', 'openai');

        $activeProvider = collect($providers)->firstWhere('name', $default);

        return view('demo.ai.failover', compact('providers', 'default', 'activeProvider'));
    }
}
