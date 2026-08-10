<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">🧠 Agent Demo</h1>
        </div>
        <p class="mt-1 text-sm text-indigo-600 font-medium">Teaching point: Agent = a reusable, named AI capability class</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Ask the HrAssistant</h2>

                {{-- Chips --}}
                <div class="flex flex-wrap gap-2 mb-3" id="chips">
                    @foreach([
                        'What is the maternity leave policy?',
                        'How many days of annual leave do employees get?',
                        'What should I do if an employee is frequently absent?',
                    ] as $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-sm bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full hover:bg-indigo-100 transition-colors"
                        data-text="{{ $chip }}">
                        {{ $chip }}
                    </button>
                    @endforeach
                </div>

                <textarea id="prompt"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                    rows="4" placeholder="Type your HR question here…"></textarea>

                <div class="mt-3 flex items-center gap-3">
                    <button id="submit-btn"
                        class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        onclick="submitPrompt()">
                        Ask HrAssistant →
                    </button>
                    <span id="status-text" class="text-sm text-gray-400 hidden">⏳ Calling model…</span>
                </div>
            </div>

            {{-- Result --}}
            <div id="result-panel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-700">Response</h2>
                    <span id="result-meta" class="text-xs text-gray-400"></span>
                </div>
                <div id="result-text" class="prose prose-sm max-w-none text-gray-800 whitespace-pre-wrap leading-relaxed"></div>
            </div>

            {{-- Error --}}
            <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="text-sm font-medium text-red-700">⚠️ Error</p>
                <p id="error-text" class="text-sm text-red-600 mt-1"></p>
            </div>

            {{-- Explainer --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-indigo-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-indigo-700 space-y-1 list-disc list-inside">
                    <li><code class="bg-indigo-100 px-1 rounded">HrAssistant</code> is a PHP class that implements the <code class="bg-indigo-100 px-1 rounded">Agent</code> contract</li>
                    <li>It has an <code class="bg-indigo-100 px-1 rounded">instructions()</code> method defining its HR persona</li>
                    <li>Calling <code class="bg-indigo-100 px-1 rounded">HrAssistant::make()->prompt($text)</code> sends the request to Gemini</li>
                    <li>The agent can be reused anywhere in your app — controllers, jobs, commands</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.getElementById('prompt').value = chip.dataset.text;
        });
    });

    async function submitPrompt() {
        const prompt = document.getElementById('prompt').value.trim();
        if (!prompt) return;

        const btn = document.getElementById('submit-btn');
        const status = document.getElementById('status-text');
        const resultPanel = document.getElementById('result-panel');
        const errorPanel = document.getElementById('error-panel');

        btn.disabled = true;
        status.classList.remove('hidden');
        resultPanel.classList.add('hidden');
        errorPanel.classList.add('hidden');

        try {
            const res = await fetch('{{ route("demo.ai.agent.prompt") }}', {
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
                errorPanel.classList.remove('hidden');
            } else {
                document.getElementById('result-text').textContent = data.text;
                document.getElementById('result-meta').textContent =
                    `Model: ${data.model} · ${data.elapsed_ms}ms`;
                resultPanel.classList.remove('hidden');
            }
        } catch (e) {
            document.getElementById('error-text').textContent = e.message;
            errorPanel.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            status.classList.add('hidden');
        }
    }

    document.getElementById('prompt').addEventListener('keydown', e => {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) submitPrompt();
    });
    </script>
    @endpush
</x-app-layout>
