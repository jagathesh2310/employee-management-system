<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Semantic FAQ Search') }}
            </h2>
            <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-gray-900">← Back to FAQs</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Search Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <p class="text-sm text-gray-500 mb-4">
                        Uses AI-powered vector similarity (pgvector) to find FAQs semantically related to your query —
                        even when the exact words don't match.
                    </p>
                    <form method="GET" action="{{ route('faqs.search') }}" class="flex gap-3 items-end">
                        <div class="flex-1">
                            <label for="query" class="block text-sm font-medium text-gray-700 mb-1">Ask a question</label>
                            <input type="text" name="query" id="query" value="{{ $queryText }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                   placeholder="e.g. How do I apply for leave?" autofocus>
                            @error('query')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="limit" class="block text-sm font-medium text-gray-700 mb-1">Results</label>
                            <select id="limit" name="limit"
                                    class="block rounded-md border-gray-300 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                                @foreach([3, 5, 10] as $n)
                                    <option value="{{ $n }}" @selected($limit === $n)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                                class="bg-purple-600 hover:bg-purple-800 text-white font-bold py-2 px-6 rounded">
                            Search
                        </button>
                    </form>
                </div>
            </div>

            {{-- Results --}}
            @if ($queryText)
                <h3 class="text-lg font-semibold text-gray-700 mb-3">
                    Results for: <em class="text-purple-700">{{ $queryText }}</em>
                </h3>

                @if ($results->isEmpty())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center text-gray-500">
                            <p>No matching FAQs found. This could mean:</p>
                            <ul class="mt-2 text-sm list-disc list-inside text-left max-w-sm mx-auto">
                                <li>No FAQs have embeddings yet (wait for the queue worker)</li>
                                <li>The topic isn't covered in the FAQ database</li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($results as $index => $faq)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 {{ $index === 0 ? 'border-purple-500' : 'border-gray-200' }}">
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-semibold text-gray-900 text-base">
                                            {{ $index + 1 }}. {{ $faq->question }}
                                        </h4>
                                        <div class="flex gap-2 ml-4 shrink-0">
                                            @if ($faq->is_active)
                                                <span class="px-2 text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @endif
                                            <a href="{{ route('faqs.show', $faq) }}"
                                               class="text-xs text-blue-600 hover:text-blue-900">View →</a>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm leading-relaxed">
                                        {{ Str::limit($faq->answer, 300) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
