<li class="{{ isset($schoolsWithData[$school['school_id']]) ? 'bg-green-50 border-l-4 border-green-400 ' : '' }}flex items-center justify-between text-sm">
    <div class="flex items-center space-x-3">
        <div class="h-8 w-8 flex-shrink-0 bg-gray-100 rounded-full flex items-center justify-center text-xs text-gray-600">S</div>
        <div class="text-gray-700">{{ $school['school_name'] }}</div>
    </div>
    <div class="text-xs space-x-2">
        @php
            $hasStateForSchool = (!empty($selectedState[$school['school_id']] ?? null) || ($selectedStateGlobal ?? '') !== '');
        @endphp
        @if($hasStateForSchool)
            <label class="inline-flex items-center">
                <input wire:model="uploads.{{ $school['school_id'] }}" type="file" id="upload-{{ $school['school_id'] }}" name="form1" class="hidden" onchange="focalHandleUploadChange(this, 'save-{{ $school['school_id'] }}')" />

                @if(isset($schoolsWithData[$school['school_id']]))
                    <button type="button" wire:click="$emit('openBaseline', {{ $school['school_id'] }})" class="px-3 py-1 mx-1 bg-blue-600 text-white text-xs rounded">Baseline</button>
                    <button type="button" wire:click="$emit('openMidline', {{ $school['school_id'] }})" class="px-3 py-1 mx-1 bg-purple-600 text-white text-xs rounded">Midline</button>
                    <button type="button" wire:click="$emit('openForm7', {{ $school['school_id'] }})" class="px-3 py-1 mx-1 bg-red-600 text-white text-xs rounded">Form7</button>
                    <button type="button" wire:click="$emit('openForm2', {{ $school['school_id'] }})" class="px-3 py-1 mx-1 bg-yellow-600 text-white text-xs rounded">Form 2</button>
                @endif

                <button type="button" onclick="document.getElementById('upload-{{ $school['school_id'] }}').click()" class="px-3 py-1 bg-indigo-600 text-white text-xs rounded">Upload Form 1</button>
            </label>
            <button id="save-{{ $school['school_id'] }}" wire:click.prevent="saveForm1({{ $school['school_id'] }})" type="button" class="px-3 py-1 bg-green-600 text-white text-xs rounded {{ empty($uploads[$school['school_id']] ?? null) ? 'hidden' : '' }}">Save</button>
        @else
            <span class="text-gray-500 text-xs">Select a state to enable uploads</span>
        @endif
    </div>
</li>
