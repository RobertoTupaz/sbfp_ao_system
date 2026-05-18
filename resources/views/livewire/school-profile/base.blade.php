<div class="space-y-6">

    @if(session('success'))
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-5">School Information</h3>

        <div class="space-y-5 max-w-xl">

            {{-- School selector --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                <select
                    wire:model.live="school_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">— Select a school —</option>
                    @foreach($schools as $school)
                        <option value="{{ $school['school_id'] }}">{{ $school['school_name'] }}</option>
                    @endforeach
                </select>
                @error('school_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- School Head Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School Head Name</label>
                <input
                    type="text"
                    wire:model="school_head_name"
                    placeholder="Enter school head name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                @error('school_head_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- School Focal Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School Focal Name</label>
                <input
                    type="text"
                    wire:model="school_focal_name"
                    placeholder="Enter school focal name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                @error('school_focal_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- School Email Address --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School Email Address</label>
                <input
                    type="email"
                    wire:model="school_email"
                    placeholder="Enter school email address"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                @error('school_email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                    <svg wire:loading wire:target="save" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

</div>
