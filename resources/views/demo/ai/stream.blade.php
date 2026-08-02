<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">⚡ Streaming</h1>
        </div>
        <p class="mt-1 text-sm text-yellow-600 font-medium">Teaching point: stream() returns SSE events consumed token-by-token in the browser</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([
                        'Explain our performance review process in detail',
                        'Write a comprehensive guide on employee onboarding',
                        'Summarize the key points of a good remote work policy',
                    ] as $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-sm bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full hover:bg-yellow-100 transition-colors"
                        data-text="{{ $chip }}">{{ $chip }}</button>
                    @endforeach
                </div>
                <textarea id="prompt"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-yellow-500 resize-none"
                    rows="3" placeholder="Ask something that generates a long response to see streaming clearly…"></textarea>
                <div class="mt-3 flex items-center gap-4">
                    <button id="submit-btn"
                        class="px-5 py-2 bg-yellow-500 text-white text-sm font-semibold rounded-lg hover:bg-yellow-600 disabled:opacity-50 transition-colors"
                        onclick="startStream()">
                        Stream Response →
                    </button>
                    <span id="stream-indicator" class="hidden flex items-center gap-1.5 text-sm text-yellow-600 font-medium">
                        <span class="inline-block w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                        Streaming…
                    </span>
                    <span id="char-count" class="text-xs text-gray-400 ml-auto hidden"><span id="chars">0</span> chars</span>
                </div>
            </div>

            {{-- Streamed Output --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-700">Live Output</h2>
                    <span id="done-badge" class="hidden text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">✓ Complete</span>
                </div>
                <div id="stream-output"
                    class="min-h-24 text-sm text-gray-800 whitespace-pre-wrap leading-relaxed font-mono border border-gray-100 bg-gray-50 rounded-lg p-4">
                    <span class="text-gray-400 italic">Output will appear here token by token…</span>
                </div>
            </div>

            {{-- Error --}}
            <div id="error-panel" class="hidden bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="text-sm font-medium text-red-700">⚠️ Error</p>
                <p id="error-text" class="text-sm text-red-600 mt-1"></p>
            </div>

            {{-- Explainer --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-yellow-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                    <li>The controller calls <code class="bg-yellow-100 px-1 rounded">HrAssistant::make()->stream($prompt)</code></li>
                    <li>It returns a <code class="bg-yellow-100 px-1 rounded">StreamableAgentResponse</code> which produces SSE events</li>
                    <li>The browser consumes events via <code class="bg-yellow-100 px-1 rounded">fetch()</code> + <code class="bg-yellow-100 px-1 rounded">ReadableStream</code> — no page refresh needed</li>
                    <li>Each <code class="bg-yellow-100 px-1 rounded">TextDelta</code> event adds a token to the output in real time</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.querySelectorAll('.chip').forEach(c =>
        c.addEventListener('click', () => document.getElementById('prompt').value = c.dataset.text)
    );

    async function startStream() {
        const prompt = document.getElementById('prompt').value.trim();
        if (!prompt) return;

        const btn = document.getElementById('submit-btn');
        const output = document.getElementById('stream-output');
        const indicator = document.getElementById('stream-indicator');
        const charCount = document.getElementById('char-count');
        const doneBadge = document.getElementById('done-badge');
        const errorPanel = document.getElementById('error-panel');

        btn.disabled = true;
        output.textContent = '';
        indicator.classList.remove('hidden');
        charCount.classList.remove('hidden');
        doneBadge.classList.add('hidden');
        errorPanel.classList.add('hidden');
        document.getElementById('chars').textContent = '0';

        const url = new URL('{{ route("demo.ai.stream.generate") }}', window.location.origin);
        url.searchParams.set('prompt', prompt);

        try {
            const res = await fetch(url.toString(), {
                headers: { 'Accept': 'text/event-stream' },
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() ?? '';

                for (const line of lines) {
                    const trimmed = line.trim();
                    if (!trimmed.startsWith('data: ')) continue;
                    const payload = trimmed.slice(6).trim();
                    if (payload === '[DONE]') break;

                    try {
                        const event = JSON.parse(payload);
                        const text = event.delta ?? event.text;
                        if (event.type === 'text_delta' && text) {
                            output.textContent += text;
                            document.getElementById('chars').textContent = output.textContent.length;
                            output.scrollTop = output.scrollHeight;
                        }
                    } catch {}
                }
            }

            if (buffer.trim().startsWith('data: ')) {
                const payload = buffer.trim().slice(6).trim();
                if (payload !== '[DONE]') {
                    try {
                        const event = JSON.parse(payload);
                        const text = event.delta ?? event.text;
                        if (event.type === 'text_delta' && text) {
                            output.textContent += text;
                            document.getElementById('chars').textContent = output.textContent.length;
                        }
                    } catch {}
                }
            }

            doneBadge.classList.remove('hidden');
        } catch (e) {
            document.getElementById('error-text').textContent = e.message;
            errorPanel.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            indicator.classList.add('hidden');
        }
    }
    </script>
    @endpush
</x-app-layout>
