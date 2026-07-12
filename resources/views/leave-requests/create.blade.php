<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($leaveRequest) ? __('Edit Leave Request') : __('Submit Leave Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ isset($leaveRequest) ? route('leave-requests.update', $leaveRequest) : route('leave-requests.store') }}" method="POST">
                        @csrf
                        @if(isset($leaveRequest))
                            @method('PUT')
                        @endif

                        @if(Auth::user()->isAdmin() || Auth::user()->isManager())
                            <div class="mb-4">
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                                <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ (old('employee_id', $leaveRequest->employee_id ?? '') == $emp->id) ? 'selected' : '' }}>{{ $emp->first_name }} {{ $emp->last_name }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <input type="hidden" name="employee_id" value="{{ App\Models\Employee::where('email', Auth::user()->email)->first()->id ?? '' }}">
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="leave_type" class="block text-sm font-medium text-gray-700">Leave Type</label>
                                <select name="leave_type" id="leave_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Select Type</option>
                                    @foreach(['annual', 'sick', 'personal', 'unpaid'] as $type)
                                        <option value="{{ $type }}" {{ (old('leave_type', $leaveRequest->leave_type ?? '') == $type) ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                                @error('leave_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Dummy div for grid spacing -->
                            <div class="hidden md:block"></div>

                            <div class="mb-4">
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Date From</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', isset($leaveRequest) ? $leaveRequest->start_date->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Date To</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', isset($leaveRequest) ? $leaveRequest->end_date->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                            <textarea name="reason" id="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('reason', $leaveRequest->reason ?? '') }}</textarea>
                            @error('reason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('leave-requests.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ isset($leaveRequest) ? 'Update' : 'Submit' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
