<div class="mx-auto p-6">
    @if (session()->has('copy_message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">{{ session('copy_message') }}</div>
    @endif
    @if (session()->has('copy_error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">{{ session('copy_error') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="text-sm text-gray-600">
            Viewing <span class="font-semibold capitalize">{{ $state }}</span> data.
            Copy the weight and height of every pupil from {{ $source }}; BMI and nutritional status are recalculated.
            Existing {{ $state }} weights and heights will be overwritten.
        </div>
        <button type="button" wire:click="copy" wire:loading.attr="disabled"
            wire:confirm="Copy {{ $source }} weight and height into {{ $state }}? Existing {{ $state }} weights and heights will be overwritten."
            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
            Copy {{ $source }} data
        </button>
    </div>
</div>
