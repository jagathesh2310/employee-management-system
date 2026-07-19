<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('FAQ') }}
            </h2>
            <div class="flex gap-2">
                @can('update', $faq)
                    <a href="{{ route('faqs.edit', $faq) }}"
                       class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Edit</a>
                @endcan
                <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-gray-900 py-2">← Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex items-center gap-3 mb-4">
                        @if ($faq->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                        @if ($faq->hasEmbedding())
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Embedding Ready</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Embedding Pending</span>
                        @endif
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $faq->question }}</h3>

                    <div class="prose max-w-none text-gray-700">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 text-xs text-gray-400 flex gap-6">
                        <span>Created: {{ $faq->created_at->format('d M Y H:i') }}</span>
                        <span>Updated: {{ $faq->updated_at->format('d M Y H:i') }}</span>
                        @if ($faq->embedding_generated_at)
                            <span>Embedding: {{ $faq->embedding_generated_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
