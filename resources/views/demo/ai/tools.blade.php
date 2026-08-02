<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">🔧 Tools Demo</h1>
        </div>
        <p class="mt-1 text-sm text-blue-600 font-medium">Teaching point: Tools let agents query real data from your Laravel app</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Prompt with Tool Access</h2>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([
                        'Find the employee with email alice@example.com',
                        'List all pending leave requests',
                        'What is the maternity leave policy?',
                    ] as $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-sm bg-blue-50 text-blue-700 border border-blue-200 rounded-full hover:bg-blue-100 transition-colors"
                        data-text="{{ $chip }}">{{ $chip }}</button>
                    @endforeach
                </div>
                <textarea id="prompt"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 resize-none"
                    rows="3" placeholder="Ask something that requires looking up employee or leave data…"></textarea>
                <div class="mt-3 flex items-center gap-3">
                    <button id="submit-btn"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors"
                        onclick="submitPrompt()">
                        Ask with Tools →
                    </button>
                    <span id="status-text" class="text-sm text-gray-400 hidden">⏳ Running tools…</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Final Answer --}}
                <div id="result-panel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-semibold text-gray-700">Final Answer</h2>
                        <span id="result-meta" class="text-xs text-gray-400"></span>
                    </div>
                    <div id="result-text" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed"></div>
                </div>

                {{-- Tool Trace --}}
                <div id="trace-panel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-base font-semibold text-gray-700 mb-3">🔍 Tools Used</h2>
                    <div id="tool-trace" class="space-y-3"></div>
                    <p id="no-tools" class="text-sm text-gray-400 italic hidden">No tools were called for this prompt.</p>
                </div>
            </div>

            {{-- Error --}}
            <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="text-sm font-medium text-red-700">⚠️ Error</p>
                <p id="error-text" class="text-sm text-red-600 mt-1"></p>
            </div>

            {{-- Explainer --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-blue-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                    <li>The <code class="bg-blue-100 px-1 rounded">HrAssistant</code> has 3 tools: <code class="bg-blue-100 px-1 rounded">LookupEmployee</code>, <code class="bg-blue-100 px-1 rounded">ListPendingLeaveRequests</code>, <code class="bg-blue-100 px-1 rounded">SearchFaq</code></li>
                    <li>The model decided which tool(s) to call based on your question</li>
                    <li>Each tool executed PHP code against your real database</li>
                    <li>The model synthesized the tool results into a final answer</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.querySelectorAll('.chip').forEach(c =>
        c.addEventListener('click', () => document.getElementById('prompt').value = c.dataset.text)
    );

    async function submitPrompt() {
        const prompt = document.getElementById('prompt').value.trim();
        if (!prompt) return;

        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        document.getElementById('status-text').classList.remove('hidden');
        document.getElementById('result-panel').classList.add('hidden');
        document.getElementById('trace-panel').classList.add('hidden');
        document.getElementById('error-panel').classList.add('hidden');

        try {
            const res = await fetch('{{ route("demo.ai.tools.prompt") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ prompt }),
            });

            const data = await res.json();

            if (!res.ok || data.error) {
                document.getElementById('error-text').textContent = data.error ?? 'Request failed.';
                document.getElementById('error-panel').classList.remove('hidden');
            } else {
                document.getElementById('result-text').textContent = data.text;
                document.getElementById('result-meta').textContent = `${data.elapsed_ms}ms`;
                document.getElementById('result-panel').classList.remove('hidden');

                // Render tool trace
                const trace = document.getElementById('tool-trace');
                trace.innerHTML = '';
                const calls = data.tool_calls ?? [];
                const results = data.tool_results ?? [];

                if (calls.length === 0) {
                    document.getElementById('no-tools').classList.remove('hidden');
                } else {
                    calls.forEach((tc, i) => {
                        const result = results.find(r => r.name === tc.name);
                        trace.innerHTML += `
                        <div class="border border-blue-100 rounded-lg p-3 bg-blue-50">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold px-2 py-0.5 bg-blue-600 text-white rounded">Tool ${i+1}</span>
                                <span class="text-sm font-semibold text-blue-800">${tc.name}</span>
                            </div>
                            <div class="text-xs text-blue-600 mb-1"><strong>Args:</strong> <code>${JSON.stringify(tc.arguments)}</code></div>
                            ${result ? `<div class="text-xs text-gray-600 bg-white border border-blue-100 rounded p-2 mt-1 max-h-24 overflow-y-auto">${result.result.substring(0, 300)}${result.result.length > 300 ? '…' : ''}</div>` : ''}
                        </div>`;
                    });
                }
                document.getElementById('trace-panel').classList.remove('hidden');
            }
        } catch (e) {
            document.getElementById('error-text').textContent = e.message;
            document.getElementById('error-panel').classList.remove('hidden');
        } finally {
            btn.disabled = false;
            document.getElementById('status-text').classList.add('hidden');
        }
    }
    </script>
    @endpush
</x-app-layout>
