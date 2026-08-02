<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">🔄 Failover Config</h1>
        </div>
        <p class="mt-1 text-sm text-gray-600 font-medium">Teaching point: Laravel AI automatically tries the next provider on failure</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Active Provider --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4">Default Provider</h2>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-2xl">🤖</div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 capitalize">{{ $default }}</p>
                        <p class="text-sm text-gray-500">
                            @if($activeProvider && $activeProvider['has_key'])
                                <span class="text-green-600 font-medium">✓ API key configured</span>
                            @else
                                <span class="text-red-500">✗ No API key</span>
                            @endif
                        </p>
                    </div>
                    <div class="ml-auto">
                        <span class="text-xs font-mono text-gray-400">AI_DEFAULT={{ strtoupper($default) }}</span>
                    </div>
                </div>
            </div>

            {{-- Provider Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-base font-semibold text-gray-700">All Configured Providers</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Env Key</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($providers as $provider)
                        <tr class="{{ $provider['name'] === $default ? 'bg-indigo-50' : '' }}">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900 capitalize">
                                {{ $provider['name'] }}
                                @if($provider['name'] === $default)
                                    <span class="ml-1 text-xs text-indigo-600 font-normal">(default)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-600 font-mono">{{ $provider['driver'] }}</td>
                            <td class="px-5 py-3 text-xs text-gray-500 font-mono">{{ $provider['key_env'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @if($provider['has_key'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Configured
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        No key
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Failover chain diagram --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-4">How Failover Works</h2>
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="flex items-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-800 rounded-lg text-sm font-medium">
                        🤖 Primary: {{ $default }}
                    </div>
                    <span class="text-gray-400 text-lg">→</span>
                    <div class="px-3 py-2 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-lg text-sm">
                        ⚠️ Rate limit / Error
                    </div>
                    <span class="text-gray-400 text-lg">→</span>
                    <div class="px-3 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-medium">
                        🔄 Try next provider
                    </div>
                    <span class="text-gray-400 text-lg">→</span>
                    <div class="px-3 py-2 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm">
                        ✅ Response returned
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600 bg-gray-50 rounded-lg p-4 font-mono text-xs">
<pre>// In your Agent class:
public function provider(): array
{
    return [
        'gemini'    => null,  // try first
        'openai'    => null,  // fallback
        'anthropic' => null,  // final fallback
    ];
}</pre>
                </div>
            </div>

            {{-- Explainer --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">💡 What does this mean?</h3>
                <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                    <li>Define an ordered list of providers in your agent's <code class="bg-gray-100 px-1 rounded">provider()</code> method</li>
                    <li>If the primary provider throws a <code class="bg-gray-100 px-1 rounded">FailoverableException</code>, the SDK automatically tries the next</li>
                    <li>The <code class="bg-gray-100 px-1 rounded">AgentFailedOver</code> event is fired, which you can log or monitor</li>
                    <li>Currently this app has <strong>Gemini</strong> configured. Add more keys to enable real failover.</li>
                </ul>
            </div>

        </div>
    </div>
</x-app-layout>
