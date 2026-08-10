<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">⏳ Queued AI Analysis</h1>
        </div>
        <p class="mt-1 text-sm text-orange-600 font-medium">Teaching point: AI tasks run in background — UI polls for status</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Select a Leave Request to Analyze</h2>
                @if($leaveRequests->isEmpty())
                    <p class="text-sm text-gray-500">No pending leave requests. Run the <code class="bg-gray-100 px-1 rounded">DemoAiSeeder</code> first.</p>
                @else
                    <select id="leave-select"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 mb-4">
                        <option value="">— Choose a leave request —</option>
                        @foreach($leaveRequests as $lr)
                        <option value="{{ $lr['id'] }}"
                                data-reason="{{ $lr['reason'] }}"
                                data-analysis="{{ json_encode($lr['ai_analysis']) }}">
                            {{ $lr['label'] }}@if($lr['has_analysis']) (Analyzed ✓)@endif
                        </option>
                        @endforeach
                    </select>

                    {{-- Reason preview --}}
                    <div id="reason-preview" class="hidden mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600 italic"></div>

                    <button id="analyze-btn"
                        class="px-5 py-2 bg-orange-500 text-white text-sm font-semibold rounded-lg hover:bg-orange-600 disabled:opacity-50 transition-colors"
                        onclick="analyzeRequest()">
                        Analyze with AI →
                    </button>
                @endif
            </div>

            {{-- Status Panel --}}
            <div id="status-panel" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span id="status-icon" class="text-2xl">⏳</span>
                    <div>
                        <p class="text-base font-semibold text-gray-800" id="status-label">Queued</p>
                        <p class="text-xs text-gray-400" id="status-sub">Waiting for the job to start…</p>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
                    <div id="progress-bar" class="h-2 bg-orange-400 rounded-full transition-all duration-500" style="width: 20%"></div>
                </div>

                {{-- Result --}}
                <div id="analysis-result" class="hidden space-y-3">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <p class="text-xs font-semibold text-orange-500 uppercase mb-1">Summary</p>
                        <p id="res-summary" class="text-sm text-gray-800"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white border border-gray-200 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-1">Recommendation</p>
                            <span id="res-action" class="text-sm font-bold"></span>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-1">Confidence</p>
                            <span id="res-confidence" class="text-sm font-bold text-gray-700"></span>
                        </div>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-400 mb-1">Citations</p>
                        <ul id="res-citations" class="space-y-0.5"></ul>
                    </div>
                </div>
            </div>

            {{-- Explainer --}}
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-orange-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-orange-700 space-y-1 list-disc list-inside">
                    <li><code class="bg-orange-100 px-1 rounded">AnalyzeLeaveRequestJob</code> dispatches on the <code class="bg-orange-100 px-1 rounded">sync</code> connection for this demo</li>
                    <li>The job stores progress in Cache: <code class="bg-orange-100 px-1 rounded">queued → processing → done</code></li>
                    <li>The UI polls <code class="bg-orange-100 px-1 rounded">/demo/ai/queue/status</code> every second</li>
                    <li>In production, switch to <code class="bg-orange-100 px-1 rounded">database</code> / <code class="bg-orange-100 px-1 rounded">redis</code> queue and a real worker</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    document.getElementById('leave-select')?.addEventListener('change', function() {
        const selectedOpt = this.options[this.selectedIndex];
        const reason = selectedOpt?.dataset.reason;
        const analysisRaw = selectedOpt?.dataset.analysis;

        const preview = document.getElementById('reason-preview');
        if (reason) {
            preview.textContent = '"' + reason + '"';
            preview.classList.remove('hidden');
        } else {
            preview.classList.add('hidden');
        }

        if (analysisRaw && analysisRaw !== 'null' && analysisRaw !== 'undefined') {
            try {
                const analysis = typeof analysisRaw === 'string' ? JSON.parse(analysisRaw) : analysisRaw;
                if (analysis && (analysis.summary || analysis.recommended_action)) {
                    showStatus('done', 'Done ✓', 'Loaded completed AI analysis from database', 100);
                    renderResult(analysis);
                    return;
                }
            } catch (e) {
                console.error(e);
            }
        }

        document.getElementById('status-panel').classList.add('hidden');
        document.getElementById('analysis-result').classList.add('hidden');
    });

    let pollInterval = null;

    async function analyzeRequest() {
        const select = document.getElementById('leave-select');
        const id = select.value;
        if (!id) { alert('Please select a leave request.'); return; }

        const btn = document.getElementById('analyze-btn');
        btn.disabled = true;

        showStatus('queued', 'Queued', 'Submitting to job queue…', 10);

        try {
            const res = await fetch('{{ route("demo.ai.queue.analyze") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ leave_request_id: id }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error ?? 'Failed');

            showStatus('processing', 'Processing', 'AI is analyzing the leave request…', 50);
            startPolling(data.cache_key, btn);
        } catch (e) {
            showStatus('error', 'Error', e.message, 0);
            btn.disabled = false;
        }
    }

    function startPolling(cacheKey, btn) {
        clearInterval(pollInterval);
        pollInterval = setInterval(async () => {
            const url = new URL('{{ route("demo.ai.queue.status") }}', window.location.origin);
            url.searchParams.set('cache_key', cacheKey);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (data.status === 'done') {
                clearInterval(pollInterval);
                showStatus('done', 'Done ✓', 'Analysis complete!', 100);
                renderResult(data.result);
                btn.disabled = false;

                const select = document.getElementById('leave-select');
                if (select && select.selectedIndex >= 0) {
                    const opt = select.options[select.selectedIndex];
                    opt.dataset.analysis = JSON.stringify(data.result);
                    if (!opt.textContent.includes('(Analyzed')) {
                        opt.textContent += ' (Analyzed ✓)';
                    }
                }
            } else if (data.status === 'failed') {
                clearInterval(pollInterval);
                showStatus('error', 'Failed', data.error ?? 'Job failed.', 0);
                btn.disabled = false;
            }
        }, 1000);
    }

    function showStatus(type, label, sub, progress) {
        const icons = { queued: '⏳', processing: '⚙️', done: '✅', error: '❌' };
        document.getElementById('status-icon').textContent = icons[type] ?? '⏳';
        document.getElementById('status-label').textContent = label;
        document.getElementById('status-sub').textContent = sub;
        document.getElementById('progress-bar').style.width = progress + '%';
        document.getElementById('status-panel').classList.remove('hidden');
    }

    function renderResult(result) {
        if (!result) return;
        document.getElementById('res-summary').textContent = result.summary ?? '—';
        const action = document.getElementById('res-action');
        action.textContent = result.recommended_action ?? '—';
        const colors = { Approve: 'text-green-700', Reject: 'text-red-700', 'Request More Info': 'text-yellow-700' };
        action.className = 'text-sm font-bold ' + (colors[result.recommended_action] ?? '');
        document.getElementById('res-confidence').textContent = Math.round((result.confidence ?? 0) * 100) + '%';
        const cList = document.getElementById('res-citations');
        cList.innerHTML = (result.citations ?? []).map(c => `<li class="text-xs text-gray-600">• ${c}</li>`).join('');
        document.getElementById('analysis-result').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
