<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('demo.ai.hub') }}" class="text-gray-400 hover:text-gray-600">← Hub</a>
            <h1 class="text-xl font-bold text-gray-900">💬 Multi-turn Chat</h1>
        </div>
        <p class="mt-1 text-sm text-green-600 font-medium">Teaching point: RemembersConversations persists history across turns</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            {{-- Conversation ID badge --}}
            <div class="flex items-center justify-between bg-white border border-gray-200 rounded-xl px-4 py-3">
                <div class="text-xs text-gray-500">
                    Conversation:
                    <code id="conv-id-display" class="font-mono text-gray-700 ml-1">New session</code>
                </div>
                <button onclick="resetChat()"
                    class="text-xs text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1 rounded-full transition-colors">
                    Reset ↺
                </button>
            </div>

            {{-- Chat messages --}}
            <div id="messages" class="bg-white rounded-xl border border-gray-200 p-4 min-h-64 max-h-[480px] overflow-y-auto space-y-4 flex flex-col">
                <div class="text-center text-sm text-gray-400 py-8" id="empty-state">
                    Start a conversation. Try a follow-up to see context carry over.
                </div>
            </div>

            {{-- Input --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach([
                        'What is the annual leave policy?',
                        'How many days is that in total?',
                        'Can it be carried over to next year?',
                    ] as $chip)
                    <button type="button"
                        class="chip px-3 py-1 text-xs bg-green-50 text-green-700 border border-green-200 rounded-full hover:bg-green-100 transition-colors"
                        data-text="{{ $chip }}">{{ $chip }}</button>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <input id="message-input" type="text"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Type a message… (Enter to send)"
                        onkeydown="if(event.key==='Enter') sendMessage()">
                    <button id="send-btn"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                        onclick="sendMessage()">
                        Send
                    </button>
                </div>
                <p id="typing-indicator" class="text-xs text-gray-400 mt-2 hidden">⏳ Thinking…</p>
            </div>

            {{-- Explainer --}}
            <div class="bg-green-50 border border-green-200 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-green-800 mb-2">💡 What just happened?</h3>
                <ul class="text-sm text-green-700 space-y-1 list-disc list-inside">
                    <li><code class="bg-green-100 px-1 rounded">ChatAgent</code> uses the <code class="bg-green-100 px-1 rounded">RemembersConversations</code> trait</li>
                    <li>Each call to <code class="bg-green-100 px-1 rounded">agent->continue($conversationId, $user)</code> loads prior messages from the DB</li>
                    <li>The conversation is stored in <code class="bg-green-100 px-1 rounded">agent_conversations</code> + <code class="bg-green-100 px-1 rounded">agent_conversation_messages</code></li>
                    <li>Try asking a vague follow-up — the model knows what "that" refers to</li>
                </ul>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
    let conversationId = null;

    document.querySelectorAll('.chip').forEach(c =>
        c.addEventListener('click', () => document.getElementById('message-input').value = c.dataset.text)
    );

    function addMessage(role, text, ms) {
        const empty = document.getElementById('empty-state');
        if (empty) empty.remove();

        const wrap = document.getElementById('messages');
        const isUser = role === 'user';
        wrap.innerHTML += `
        <div class="flex ${isUser ? 'justify-end' : 'justify-start'}">
            <div class="max-w-sm lg:max-w-md">
                <div class="text-xs text-gray-400 mb-1 ${isUser ? 'text-right' : 'text-left'}">${isUser ? 'You' : '🤖 HR Assistant'}${ms ? ` · ${ms}ms` : ''}</div>
                <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed whitespace-pre-wrap
                    ${isUser
                        ? 'bg-green-600 text-white rounded-tr-sm'
                        : 'bg-gray-100 text-gray-800 rounded-tl-sm'}">
                    ${text}
                </div>
            </div>
        </div>`;
        wrap.scrollTop = wrap.scrollHeight;
    }

    async function sendMessage() {
        const input = document.getElementById('message-input');
        const message = input.value.trim();
        if (!message) return;

        input.value = '';
        addMessage('user', message);

        const btn = document.getElementById('send-btn');
        btn.disabled = true;
        document.getElementById('typing-indicator').classList.remove('hidden');

        try {
            const res = await fetch('{{ route("demo.ai.chat.message") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message, conversation_id: conversationId }),
            });

            const data = await res.json();

            if (!res.ok || data.error) {
                addMessage('assistant', '⚠️ Error: ' + (data.error ?? 'Request failed.'));
            } else {
                conversationId = data.conversation_id;
                document.getElementById('conv-id-display').textContent =
                    conversationId ? conversationId.substring(0, 20) + '…' : 'Active';
                addMessage('assistant', data.text, data.elapsed_ms);
            }
        } catch (e) {
            addMessage('assistant', '⚠️ Network error: ' + e.message);
        } finally {
            btn.disabled = false;
            document.getElementById('typing-indicator').classList.add('hidden');
            input.focus();
        }
    }

    async function resetChat() {
        conversationId = null;
        document.getElementById('conv-id-display').textContent = 'New session';
        document.getElementById('messages').innerHTML = `
            <div class="text-center text-sm text-gray-400 py-8" id="empty-state">
                Conversation reset. Start a new one.
            </div>`;
    }
    </script>
    @endpush
</x-app-layout>
