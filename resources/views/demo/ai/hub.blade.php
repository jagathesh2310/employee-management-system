<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🤖 Laravel AI SDK — Demo Hub</h1>
                <p class="mt-1 text-sm text-gray-500">Click any card to launch a live browser demo of <code class="bg-gray-100 px-1 rounded">laravel/ai</code> features.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                ✓ Gemini Connected
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Tier 1 --}}
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Core Features</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

                @php
                $demos = [
                    ['route' => 'demo.ai.agent',      'icon' => '🧠', 'title' => 'Agent',            'color' => 'indigo', 'teach' => 'Agent = reusable AI capability class', 'desc' => 'Type any HR question. The HrAssistant agent responds using its system instructions.'],
                    ['route' => 'demo.ai.tools',      'icon' => '🔧', 'title' => 'Tools',            'color' => 'blue',   'teach' => 'Tools let agents query real app data', 'desc' => 'Agent uses LookupEmployee, ListLeaveRequests, and SearchFAQ tools. See the full tool trace.'],
                    ['route' => 'demo.ai.structured', 'icon' => '📋', 'title' => 'Structured Output', 'color' => 'purple', 'teach' => 'Schema-enforced JSON output, rendered as UI', 'desc' => 'Describe a leave scenario. Get back structured fields: summary, action, confidence, citations.'],
                    ['route' => 'demo.ai.faq-search', 'icon' => '🔍', 'title' => 'FAQ Semantic Search','color' => 'teal', 'teach' => 'Meaning match vs. keyword match', 'desc' => 'Search HR FAQs side-by-side: ILIKE keyword results vs. pgvector semantic results.'],
                ];
                @endphp

                @foreach($demos as $demo)
                <a href="{{ route($demo['route']) }}"
                   class="group bg-white rounded-xl shadow-sm border border-gray-200 hover:border-{{ $demo['color'] }}-400 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-3xl">{{ $demo['icon'] }}</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-{{ $demo['color'] }}-50 text-{{ $demo['color'] }}-700 border border-{{ $demo['color'] }}-200">
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-{{ $demo['color'] }}-700 transition-colors">{{ $demo['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $demo['desc'] }}</p>
                    </div>
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-400 italic">💡 {{ $demo['teach'] }}</span>
                        <span class="text-{{ $demo['color'] }}-600 text-sm font-medium group-hover:translate-x-1 transition-transform inline-block">Launch →</span>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

                @php
                $tier2 = [
                    ['route' => 'demo.ai.chat',      'icon' => '💬', 'title' => 'Multi-turn Chat', 'color' => 'green',  'teach' => 'Persistent conversations across turns', 'desc' => 'Chat with the HR assistant. Ask follow-up questions — it remembers earlier context.'],
                    ['route' => 'demo.ai.stream',    'icon' => '⚡', 'title' => 'Streaming',         'color' => 'yellow', 'teach' => 'Token-by-token SSE streaming to browser', 'desc' => 'Watch the response appear token by token via Server-Sent Events.'],
                    ['route' => 'demo.ai.queue',     'icon' => '⏳', 'title' => 'Queued Analysis',   'color' => 'orange', 'teach' => 'AI tasks run in the background queue', 'desc' => 'Select a leave request. Dispatch AI analysis. Watch status: queued → done.'],
                    ['route' => 'demo.ai.approval',  'icon' => '✋', 'title' => 'Human Approval',    'color' => 'red',    'teach' => 'AI proposes — human decides', 'desc' => 'AI drafts a FAQ entry. You approve (creates it) or reject (nothing happens). Human in the loop.'],
                    ['route' => 'demo.ai.summarize', 'icon' => '✂️', 'title' => 'Summarize',         'color' => 'pink',   'teach' => 'Focused single-purpose agents', 'desc' => 'Paste any HR text. Get a concise 2-4 bullet summary from the SummarizeAgent.'],
                    ['route' => 'demo.ai.failover',  'icon' => '🔄', 'title' => 'Failover Config',   'color' => 'gray',   'teach' => 'Automatic provider failover chain', 'desc' => 'See all configured AI providers and which ones have API keys. Understand failover ordering.'],
                ];
                @endphp

                @foreach($tier2 as $demo)
                <a href="{{ route($demo['route']) }}"
                   class="group bg-white rounded-xl shadow-sm border border-gray-200 hover:border-{{ $demo['color'] }}-400 hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-3">
                            <span class="text-3xl">{{ $demo['icon'] }}</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-{{ $demo['color'] }}-50 text-{{ $demo['color'] }}-700 border border-{{ $demo['color'] }}-200">
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-{{ $demo['color'] }}-700 transition-colors">{{ $demo['title'] }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $demo['desc'] }}</p>
                    </div>
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-400 italic">💡 {{ $demo['teach'] }}</span>
                        <span class="text-{{ $demo['color'] }}-600 text-sm font-medium group-hover:translate-x-1 transition-transform inline-block">Launch →</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
