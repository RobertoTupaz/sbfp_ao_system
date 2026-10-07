<div class="flex items-center gap-2">
    <label for="survey-state-{{ $this->getId() }}" class="text-xs font-medium text-gray-500">Data:</label>
    <select id="survey-state-{{ $this->getId() }}" wire:model.live="state"
        class="py-1 ps-2 pe-8 text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 capitalize">
        @foreach($states as $s)
            <option value="{{ $s }}" class="capitalize">{{ ucfirst($s) }}</option>
        @endforeach
    </select>
</div>
