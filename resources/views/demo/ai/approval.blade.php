<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">✋ Human Approval</h1>
        </div>
        <p class="mt-1 text-sm text-red-600 font-medium">Teaching point: AI decides what to do — a human decides whether it happens</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Key message --}}
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-5 text-center">
                <p class="text-xl font-bold text-red-800">🤖 AI decides what to do</p>
                <p class="text-xl font-bold text-red-800">👤 A human decides whether it happens</p>
            </div>

            {{-- Step 1: Propose --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 bg-gray-800 text-white text-xs font-bold rounded-full flex items-center justify-center">1</span>
                    <h2 class="text-base font-semibold text-gray-700">Ask AI to Draft a FAQ Entry</h2>
                </div>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(['Remote work policy', 'Overtime compensation', 'Bereavement leave', 'Performance bonus criteria'] as $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-sm bg-gray-100 text-gray-700 border border-gray-200 rounded-full hover:bg-gray-200 transition-colors"
                        data-text="{{ $chip }}">{{ $chip }}</button>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    <input id="topic-input" type="text"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-gray-500"
                        placeholder="FAQ topic…">
                    <button id="propose-btn"
                        class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 disabled:opacity-50 transition-colors"
                        onclick="proposeFaq()">
                        Generate Draft →
                    </button>
                </div>
                <p id="proposing-text" class="text-xs text-gray-400 mt-2 hidden">⏳ AI is drafting…</p>
            </div>

            {{-- Step 2: Review --}}
            <div id="proposal-panel" class="hidden bg-white rounded-xl shadow-sm border border-amber-300 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 bg-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center">2</span>
                    <h2 class="text-base font-semibold text-gray-700">Review AI's Proposal</h2>
                    <span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full font-medium ml-auto">Pending Human Decision</span>
                </div>

                <div class="space-y-3 mb-6">
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase">Proposed Question</label>
                        <p id="proposed-question" class="text-sm text-gray-800 mt-1 font-medium p-2 bg-amber-50 rounded"></p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-400 uppercase">Proposed Answer</label>
                        <p id="proposed-answer" class="text-sm text-gray-700 mt-1 p-2 bg-amber-50 rounded leading-relaxed"></p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button id="approve-btn"
                        class="flex-1 px-4 py-2.5 bg-green-600 text-white text-sm font-bold rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                        onclick="resolveProposal('approve')">
                        ✅ Approve — Create FAQ
                    </button>
                    <button id="reject-btn"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                        onclick="resolveProposal('reject')">
                        ❌ Reject — Do Nothing
                    </button>
                </div>
            </div>

            {{-- Step 3: Outcome --}}
            <div id="outcome-panel" class="hidden rounded-xl p-6">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 text-white text-xs font-bold rounded-full flex items-center justify-center" id="outcome-step-badge">3</span>
                    <h2 class="text-base font-semibold" id="outcome-title">Outcome</h2>
                </div>
                <p id="outcome-message" class="text-sm"></p>
            </div>

            {{-- Explainer --}}
            <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-red-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                    <li>The <code class="bg-red-100 px-1 rounded">ApprovalAgent</code> drafted a FAQ but <strong>nothing was saved yet</strong></li>
                    <li>A proposal record was created in <code class="bg-red-100 px-1 rounded">ai_approval_proposals</code> with <code class="bg-red-100 px-1 rounded">status=pending</code></li>
                    <li><strong>Approve</strong>: calls <code class="bg-red-100 px-1 rounded">FaqService::create()</code> — FAQ is added to the knowledge base</li>
                    <li><strong>Reject</strong>: marks proposal <code class="bg-red-100 px-1 rounded">rejected</code> — zero side effects, nothing written to DB</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    let currentProposalId = null;

    document.querySelectorAll('.chip').forEach(c =>
        c.addEventListener('click', () => document.getElementById('topic-input').value = c.dataset.text)
    );

    async function proposeFaq() {
        const topic = document.getElementById('topic-input').value.trim();
        if (!topic) return;

        const btn = document.getElementById('propose-btn');
        btn.disabled = true;
        document.getElementById('proposing-text').classList.remove('hidden');
        document.getElementById('proposal-panel').classList.add('hidden');
        document.getElementById('outcome-panel').classList.add('hidden');

        try {
            const res = await fetch('{{ route("demo.ai.approval.propose") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ topic }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error ?? 'Failed');

            currentProposalId = data.proposal_id;
            document.getElementById('proposed-question').textContent = data.proposed.question ?? '—';
            document.getElementById('proposed-answer').textContent = data.proposed.answer ?? '—';

            document.getElementById('approve-btn').disabled = false;
            document.getElementById('reject-btn').disabled = false;
            document.getElementById('proposal-panel').classList.remove('hidden');
        } catch (e) {
            alert('Error: ' + e.message);
        } finally {
            btn.disabled = false;
            document.getElementById('proposing-text').classList.add('hidden');
        }
    }

    async function resolveProposal(action) {
        if (!currentProposalId) return;

        document.getElementById('approve-btn').disabled = true;
        document.getElementById('reject-btn').disabled = true;

        const url = action === 'approve'
            ? `/demo/ai/approval/${currentProposalId}/approve`
            : `/demo/ai/approval/${currentProposalId}/reject`;

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();

            const panel = document.getElementById('outcome-panel');
            const badge = document.getElementById('outcome-step-badge');
            const title = document.getElementById('outcome-title');
            const msg = document.getElementById('outcome-message');

            if (action === 'approve') {
                panel.className = 'rounded-xl p-6 bg-green-50 border border-green-300';
                badge.className = 'w-6 h-6 bg-green-600 text-white text-xs font-bold rounded-full flex items-center justify-center';
                title.textContent = '✅ Approved — FAQ Created';
                title.className = 'text-base font-semibold text-green-800';
                msg.textContent = data.message + ' FAQ: "' + data.faq_question + '"';
                msg.className = 'text-sm text-green-700';
            } else {
                panel.className = 'rounded-xl p-6 bg-red-50 border border-red-300';
                badge.className = 'w-6 h-6 bg-red-600 text-white text-xs font-bold rounded-full flex items-center justify-center';
                title.textContent = '❌ Rejected — No Action Taken';
                title.className = 'text-base font-semibold text-red-800';
                msg.textContent = data.message;
                msg.className = 'text-sm text-red-700';
            }

            panel.classList.remove('hidden');
            currentProposalId = null;
        } catch (e) {
            alert('Error: ' + e.message);
            document.getElementById('approve-btn').disabled = false;
            document.getElementById('reject-btn').disabled = false;
        }
    }
    </script>
    @endpush
</x-app-layout>
