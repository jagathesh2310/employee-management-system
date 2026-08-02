<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">📋 Structured Output</h1>
        </div>
        <p class="mt-1 text-sm text-purple-600 font-medium">Teaching point: Structured output returns validated JSON fields — not free text</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Describe a Leave Request Scenario</h2>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([
                        'Sarah requested 10 days of medical leave starting next Monday. She has been with the company for 2 years and has never taken sick leave before.',
                        'John applied for 3 weeks of annual leave during peak sales season in December. His team is already short-staffed.',
                        'Maria is requesting maternity leave for 12 weeks starting in 6 weeks. She is a senior developer on a critical project.',
                    ] as $chip)
                    <button type="button"
                        class="chip px-3 py-1.5 text-xs bg-purple-50 text-purple-700 border border-purple-200 rounded-full hover:bg-purple-100 transition-colors text-left"
                        data-text="{{ $chip }}">{{ Str::limit($chip, 60) }}</button>
                    @endforeach
                </div>
                <textarea id="prompt"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-purple-500 resize-none"
                    rows="4" placeholder="Describe a leave request scenario to analyze…"></textarea>
                <div class="mt-3 flex items-center gap-3">
                    <button id="submit-btn"
                        class="px-5 py-2 bg-purple-600 text-white text-sm font-semibold rounded-lg hover:bg-purple-700 disabled:opacity-50 transition-colors"
                        onclick="submitPrompt()">
                        Analyze →
                    </button>
                    <span id="status-text" class="text-sm text-gray-400 hidden">⏳ Generating structured output…</span>
                </div>
            </div>

            {{-- Structured Result Cards --}}
            <div id="result-panel" class="hidden space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-700">Structured Analysis</h2>
                    <span id="result-meta" class="text-xs text-gray-400"></span>
                </div>

                {{-- Summary --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Summary</p>
                    <p id="summary-text" class="text-gray-800 text-sm leading-relaxed"></p>
                </div>

                {{-- Action + Confidence --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Recommended Action</p>
                        <span id="action-badge" class="inline-block px-4 py-1.5 rounded-full text-sm font-bold"></span>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Confidence</p>
                        <div class="flex items-center gap-3">
                            <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden">
                                <div id="confidence-bar" class="h-full bg-purple-500 rounded-full transition-all duration-700" style="width: 0%"></div>
                            </div>
                            <span id="confidence-pct" class="text-sm font-bold text-gray-700"></span>
                        </div>
                    </div>
                </div>

                {{-- Citations --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Citations</p>
                    <ul id="citations-list" class="space-y-1"></ul>
                </div>

                {{-- Raw JSON Toggle --}}
                <details class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <summary class="text-xs font-medium text-gray-500 cursor-pointer select-none">View raw JSON response</summary>
                    <pre id="raw-json" class="mt-3 text-xs text-gray-600 overflow-x-auto"></pre>
                </details>
            </div>

            {{-- Error --}}
            <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="text-sm font-medium text-red-700">⚠️ Error</p>
                <p id="error-text" class="text-sm text-red-600 mt-1"></p>
            </div>

            {{-- Explainer --}}
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-purple-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-purple-700 space-y-1 list-disc list-inside">
                    <li><code class="bg-purple-100 px-1 rounded">LeaveAnalysisAgent</code> implements <code class="bg-purple-100 px-1 rounded">HasStructuredOutput</code> with a <code class="bg-purple-100 px-1 rounded">schema()</code> method</li>
                    <li>The schema enforces fields: <code class="bg-purple-100 px-1 rounded">summary</code>, <code class="bg-purple-100 px-1 rounded">recommended_action</code>, <code class="bg-purple-100 px-1 rounded">confidence</code>, <code class="bg-purple-100 px-1 rounded">citations</code></li>
                    <li>The model is forced to return valid JSON matching that schema — no free-form text</li>
                    <li>The response comes back as a typed PHP array you can use directly</li>
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
        document.getElementById('error-panel').classList.add('hidden');

        try {
            const res = await fetch('{{ route("demo.ai.structured.prompt") }}', {
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
                const s = data.structured;
                document.getElementById('summary-text').textContent = s.summary ?? '—';

                // Action badge
                const badge = document.getElementById('action-badge');
                const colors = { 'Approve': 'bg-green-100 text-green-800', 'Reject': 'bg-red-100 text-red-800', 'Request More Info': 'bg-yellow-100 text-yellow-800' };
                badge.textContent = s.recommended_action ?? '—';
                badge.className = 'inline-block px-4 py-1.5 rounded-full text-sm font-bold ' + (colors[s.recommended_action] ?? 'bg-gray-100 text-gray-800');

                // Confidence
                const pct = Math.round((s.confidence ?? 0) * 100);
                document.getElementById('confidence-bar').style.width = pct + '%';
                document.getElementById('confidence-pct').textContent = pct + '%';

                // Citations
                const cList = document.getElementById('citations-list');
                cList.innerHTML = '';
                (s.citations ?? []).forEach(c => {
                    cList.innerHTML += `<li class="flex items-start gap-2 text-sm text-gray-700"><span class="text-purple-400 mt-0.5">›</span><span>${c}</span></li>`;
                });

                document.getElementById('raw-json').textContent = JSON.stringify(s, null, 2);
                document.getElementById('result-meta').textContent = `${data.elapsed_ms}ms`;
                document.getElementById('result-panel').classList.remove('hidden');
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
