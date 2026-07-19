<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add FAQ') }}
            </h2>
            <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-gray-900">← Back to FAQs</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-sm text-gray-500 mb-6">
                        After saving, an embedding will be generated automatically in the background to enable semantic search.
                    </p>

                    <form action="{{ route('faqs.store') }}" method="POST">
                        @csrf

                        @include('faqs.partials._form')

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('faqs.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Save FAQ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
