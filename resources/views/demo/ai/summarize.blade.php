<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">✂️ Summarize</h1>
        </div>
        <p class="mt-1 text-sm text-pink-600 font-medium">Teaching point: Focused single-purpose agents do one thing well</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Paste Text to Summarize</h2>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([
                        'I have been experiencing severe migraines for the past two weeks that have been significantly impacting my ability to work. My doctor has prescribed rest and medication, and has recommended that I take at least 10 days off to recover properly. I have tried working from home but the screen time makes the condition worse. I have been with this company for 3 years and have never taken extended sick leave before.',
                        'Our team has been working on the quarterly product launch for the past 6 months. We are now entering the final sprint with a hard deadline in 3 weeks. We currently have 4 developers, 1 designer, and 1 QA engineer. The critical path includes completing the payment integration, user testing, and security audit. Any team member taking leave during this period would require careful handover planning.',
                    ] as $i => $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-xs bg-pink-50 text-pink-700 border border-pink-200 rounded-full hover:bg-pink-100 transition-colors"
                        data-text="{{ $chip }}">Example {{ $i + 1 }}</button>
                    @endforeach
                </div>
                <textarea id="text-input"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-pink-500 resize-none"
                    rows="8" placeholder="Paste a leave reason, HR policy, or any text to summarize (min. 50 characters)…"></textarea>
                <div class="mt-2 flex items-center justify-between">
                    <span id="char-count" class="text-xs text-gray-400">0 / 5000 chars</span>
                    <button id="submit-btn"
                        class="px-5 py-2 bg-pink-600 text-white text-sm font-semibold rounded-lg hover:bg-pink-700 disabled:opacity-50 transition-colors"
                        onclick="doSummarize()">
                        Summarize →
                    </button>
                </div>
                <p id="loading-text" class="text-xs text-gray-400 mt-2 hidden">⏳ Generating summary…</p>
            </div>

            {{-- Result --}}
            <div id="result-panel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-700">Summary</h2>
                    <span id="result-meta" class="text-xs text-gray-400"></span>
                </div>
                <div id="summary-output" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed p-4 bg-pink-50 border border-pink-100 rounded-lg"></div>
            </div>

            {{-- Error --}}
            <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="text-sm font-medium text-red-700">⚠️ Error</p>
                <p id="error-text" class="text-sm text-red-600 mt-1"></p>
            </div>

            {{-- Explainer --}}
            <div class="bg-pink-50 border border-pink-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-pink-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-pink-700 space-y-1 list-disc list-inside">
                    <li><code class="bg-pink-100 px-1 rounded">SummarizeAgent</code> has a single, focused instruction: produce 2-4 bullet points</li>
                    <li>It calls <code class="bg-pink-100 px-1 rounded">agent->prompt("Please summarize: " + text)</code></li>
                    <li>Focused agents are more reliable and cheaper than general-purpose ones</li>
                    <li>You can test and fake them independently from the rest of your app</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.querySelectorAll('.chip').forEach(c =>
        c.addEventListener('click', () => {
            document.getElementById('text-input').value = c.dataset.text;
            updateCount();
        })
    );

    document.getElementById('text-input').addEventListener('input', updateCount);
    function updateCount() {
        const len = document.getElementById('text-input').value.length;
        document.getElementById('char-count').textContent = len + ' / 5000 chars';
    }

    async function doSummarize() {
        const text = document.getElementById('text-input').value.trim();
        if (text.length < 50) { alert('Please enter at least 50 characters.'); return; }

        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        document.getElementById('loading-text').classList.remove('hidden');
        document.getElementById('result-panel').classList.add('hidden');
        document.getElementById('error-panel').classList.add('hidden');

        try {
            const res = await fetch('{{ route("demo.ai.summarize.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ text }),
            });
            const data = await res.json();
            if (!res.ok || data.error) throw new Error(data.error ?? 'Failed');

            document.getElementById('summary-output').textContent = data.summary;
            document.getElementById('result-meta').textContent =
                `${data.original_length} chars → ${data.summary_length} chars · ${data.elapsed_ms}ms`;
            document.getElementById('result-panel').classList.remove('hidden');
        } catch (e) {
            document.getElementById('error-text').textContent = e.message;
            document.getElementById('error-panel').classList.remove('hidden');
        } finally {
            btn.disabled = false;
            document.getElementById('loading-text').classList.add('hidden');
        }
    }
    </script>
    @endpush
</x-app-layout>
