<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">🔍 FAQ Semantic Search</h1>
        </div>
        <p class="mt-1 text-sm text-teal-600 font-medium">Teaching point: Semantic search matches meaning, not just keywords</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Search Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Search HR FAQs</h2>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([
                        'time off for having a baby',
                        'getting paid when sick',
                        'remote working from home',
                        'annual leave days',
                        'what happens if I am late',
                    ] as $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-sm bg-teal-50 text-teal-700 border border-teal-200 rounded-full hover:bg-teal-100 transition-colors"
                        data-text="{{ $chip }}">{{ $chip }}</button>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    <input id="query" type="text"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-500"
                        placeholder="Type a question in plain language (paraphrases work!)">
                    <button id="search-btn"
                        class="px-5 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 disabled:opacity-50 transition-colors"
                        onclick="doSearch()">
                        Compare →
                    </button>
                </div>
                <p id="searching-text" class="text-xs text-gray-400 mt-2 hidden">⏳ Running both searches…</p>
            </div>

            {{-- Side-by-side results --}}
            <div id="results-grid" class="hidden grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Keyword --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-700">🔤 Keyword (ILIKE)</span>
                        <span id="keyword-count" class="text-xs text-gray-400"></span>
                    </div>
                    <div id="keyword-results" class="p-4 space-y-3 min-h-32"></div>
                </div>

                {{-- Semantic --}}
                <div class="bg-white rounded-xl shadow-sm border border-teal-200 overflow-hidden">
                    <div class="px-5 py-3 bg-teal-50 border-b border-teal-200 flex items-center gap-2">
                        <span class="text-sm font-semibold text-teal-700">🧠 Semantic (pgvector)</span>
                        <span id="semantic-count" class="text-xs text-teal-500"></span>
                    </div>
                    <div id="semantic-results" class="p-4 space-y-3 min-h-32"></div>
                </div>
            </div>

            {{-- Explainer --}}
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-teal-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-teal-700 space-y-1 list-disc list-inside">
                    <li><strong>Keyword</strong>: uses SQL <code class="bg-teal-100 px-1 rounded">ILIKE '%query%'</code> — only matches if the exact words appear</li>
                    <li><strong>Semantic</strong>: converts query to a 768-dim vector embedding via Gemini, then finds nearest FAQ vectors using <code class="bg-teal-100 px-1 rounded">orderByVectorDistance</code></li>
                    <li>Try "time off for having a baby" — keyword misses, semantic finds the maternity leave FAQ</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.querySelectorAll('.chip').forEach(c =>
        c.addEventListener('click', () => {
            document.getElementById('query').value = c.dataset.text;
            doSearch();
        })
    );

    document.getElementById('query').addEventListener('keydown', e => {
        if (e.key === 'Enter') doSearch();
    });

    function renderResults(containerId, results, type) {
        const el = document.getElementById(containerId);
        if (results.length === 0) {
            el.innerHTML = `<p class="text-sm text-gray-400 italic text-center py-4">No results found.</p>`;
            return;
        }
        el.innerHTML = results.map((r, i) => `
        <div class="border border-gray-100 rounded-lg p-3 hover:border-${type === 'semantic' ? 'teal' : 'gray'}-300 transition-colors">
            <span class="text-xs font-bold text-gray-400">#${i+1}</span>
            <p class="text-sm font-medium text-gray-800 mt-1">${r.question}</p>
            <p class="text-xs text-gray-500 mt-1 line-clamp-2">${r.answer}</p>
        </div>`).join('');
    }

    async function doSearch() {
        const query = document.getElementById('query').value.trim();
        if (!query) return;

        const btn = document.getElementById('search-btn');
        btn.disabled = true;
        document.getElementById('searching-text').classList.remove('hidden');

        try {
            const url = new URL('{{ route("demo.ai.faq-search.search") }}', window.location.origin);
            url.searchParams.set('query', query);
            const res = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();

            document.getElementById('keyword-count').textContent = `(${data.keyword.length} results)`;
            document.getElementById('semantic-count').textContent = `(${data.semantic.length} results)`;
            renderResults('keyword-results', data.keyword, 'keyword');
            renderResults('semantic-results', data.semantic, 'semantic');
            document.getElementById('results-grid').classList.remove('hidden');
        } catch (e) {
            alert('Search failed: ' + e.message);
        } finally {
            btn.disabled = false;
            document.getElementById('searching-text').classList.add('hidden');
        }
    }
    </script>
    @endpush
</x-app-layout>
