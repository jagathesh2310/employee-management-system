{{-- Shared form partial for both create and edit FAQ views --}}

<div class="mb-4">
    <label for="question" class="block text-sm font-medium text-gray-700">Question <span class="text-red-500">*</span></label>
    <input type="text" name="question" id="question"
           value="{{ old('question', $faq->question ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
           required minlength="5" maxlength="500" placeholder="Enter the question…">
    @error('question')
        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <label for="answer" class="block text-sm font-medium text-gray-700">Answer <span class="text-red-500">*</span></label>
    <textarea name="answer" id="answer" rows="6"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              required minlength="10" placeholder="Enter the answer…">{{ old('answer', $faq->answer ?? '') }}</textarea>
    @error('answer')
        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4 flex items-center gap-3">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" id="is_active" value="1"
           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
           {{ old('is_active', $faq->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="text-sm font-medium text-gray-700">Active (visible to users)</label>
    @error('is_active')
        <span class="text-red-500 text-xs">{{ $message }}</span>
    @enderror
</div>
