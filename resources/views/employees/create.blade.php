<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($employee) ? __('Edit Employee') : __('Add Employee') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}" method="POST">
                        @csrf
                        @if(isset($employee))
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $employee->first_name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $employee->last_name ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $employee->email ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="department_id" class="block text-sm font-medium text-gray-700">Department</label>
                                <select name="department_id" id="department_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ (old('department_id', $employee->department_id ?? '') == $dept->id) ? 'selected' : '' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="position_id" class="block text-sm font-medium text-gray-700">Position</label>
                                <select name="position_id" id="position_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Select Position</option>
                                    @foreach($positions as $pos)
                                        <option value="{{ $pos->id }}" {{ (old('position_id', $employee->position_id ?? '') == $pos->id) ? 'selected' : '' }}>{{ $pos->title }}</option>
                                    @endforeach
                                </select>
                                @error('position_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="salary" class="block text-sm font-medium text-gray-700">Salary</label>
                                <input type="number" step="0.01" name="salary" id="salary" value="{{ old('salary', isset($employee) ? $employee->salary : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('salary') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="date_of_joining" class="block text-sm font-medium text-gray-700">Date of Joining</label>
                                <input type="date" name="date_of_joining" id="date_of_joining" value="{{ old('date_of_joining', isset($employee) ? $employee->date_of_joining->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('date_of_joining') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ isset($employee) ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
