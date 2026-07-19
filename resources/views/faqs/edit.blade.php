<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit FAQ') }}
            </h2>
            <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-gray-900">← Back to FAQs</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if ($faq->hasEmbedding())
                <div class="bg-purple-50 border border-purple-200 text-purple-700 px-4 py-3 rounded mb-4 text-sm">
                    ✓ Embedding generated {{ $faq->embedding_generated_at?->diffForHumans() }}.
                    Changing the question or answer will trigger a new embedding job.
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded mb-4 text-sm">
                    ⏳ Embedding is pending – the background job may still be processing.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('faqs.update', $faq) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('faqs.partials._form')

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update FAQ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
