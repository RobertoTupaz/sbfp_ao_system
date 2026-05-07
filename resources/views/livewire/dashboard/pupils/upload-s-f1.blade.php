<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
        @if (session()->has('message'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid sm:grid-cols-3 gap-4 items-center">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Excel file</label>

                <div class="flex items-center gap-3">
                    <label class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded cursor-pointer hover:bg-blue-700">
                        <span>Choose file</span>
                        <input type="file" wire:model="excel" accept=".xlsx,.xls,.csv" class="sr-only">
                    </label>

                    <div class="text-sm text-gray-600">
                        @if($excel)
                            <span class="font-medium">{{ method_exists($excel, 'getClientOriginalName') ? $excel->getClientOriginalName() : 'Selected file' }}</span>
                        @else
                            <span>No file selected</span>
                        @endif
                    </div>
                </div>

                @error('excel') <div class="text-sm text-red-600 mt-2">{{ $message }}</div> @enderror
            </div>

            <div class="flex gap-2">
                <button wire:click.prevent="save" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <svg wire:loading.class="animate-spin" wire:target="save" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M4 12H0" /></svg>
                    <span>Save</span>
                </button>
            </div>
        </div>

        {{-- Preview removed: Save reads sheet 2 and persists directly. --}}
    </div>
</div>
