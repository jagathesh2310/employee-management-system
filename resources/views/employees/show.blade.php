<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('View Employee') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
                    <p class="mt-2 text-gray-600">Email: {{ $employee->email }}</p>
                    <p class="mt-2 text-gray-600">Department: {{ $employee->department->name ?? 'N/A' }}</p>
                    <p class="mt-2 text-gray-600">Position: {{ $employee->position->title ?? 'N/A' }}</p>
                    <p class="mt-2 text-gray-600">Status: {{ ucfirst($employee->status->value) }}</p>
                    <div class="mt-4">
                        <a href="{{ route('employees.index') }}" class="text-blue-500 hover:underline">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
