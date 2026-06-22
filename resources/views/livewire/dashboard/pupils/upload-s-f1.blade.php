<div class="mx-auto p-6">
    <div class="bg-white shadow rounded-lg p-6">
        @if (session()->has('message'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid sm:grid-cols-3 gap-4 items-center">
            <div class="sm:col-span-2">
                <div class="flex items-center gap-1.5 mb-2 z-1000">
                    <label class="block text-sm font-medium text-gray-700">SF1 upload</label>
                    <x-feature-help>Upload a School Form 1 (SF1) Excel file exported from the DepEd LIS system. The file is parsed to extract pupil names, grade, section, birth dates, and demographic flags. Review the preview table before saving.</x-feature-help>
                </div>

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
                <button wire:click.prevent="importPreview" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    <svg wire:loading.class="animate-spin" wire:target="importPreview" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M4 12H0" /></svg>
                    <span>Preview</span>
                </button>
                <button wire:click.prevent="save" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <svg wire:loading.class="animate-spin" wire:target="save" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m8-8h-4M4 12H0" /></svg>
                    <span>Save</span>
                </button>
            </div>
        </div>

        @if($preview && !empty($rows))
            <div class="mt-8 bg-gray-50 rounded-lg p-6">
                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Preview: {{ count($rows) }} rows parsed (Editable)</h3>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Change all grade:</label>
                        <select wire:model.live="changeAllGrade" class="px-3 py-1.5 border border-gray-300 rounded text-sm bg-white">
                            <option value="">— select —</option>
                            <option value="kinder">Kinder</option>
                            @for ($g = 1; $g <= 12; $g++)
                                <option value="{{ $g }}">Grade {{ $g }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Change all section:</label>
                        <input type="text" wire:model.live="changeAllSection" placeholder="e.g. Sampaguita" class="px-3 py-1.5 border border-gray-300 rounded text-sm bg-white w-40">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-300">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">LRN</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Grade</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Section</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Last Name</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">First Name</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Sex</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Birthdate</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Age (Yrs)</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Age (Mos)</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Weight (kg)</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">Height (cm)</th>
                                <th class="border border-gray-300 px-4 py-2 text-left text-sm font-semibold">IP</th>
                                <th class="border border-gray-300 py-2 text-center text-sm font-semibold">4Ps</th>
                                <th class="border border-gray-300 py-2 text-center text-sm font-semibold">PARDO</th>
                                <th class="border border-gray-300 py-2 text-center text-sm font-semibold">Dewormed</th>
                                <th class="border border-gray-300 py-2 text-center text-sm font-semibold">Prev. Ben.</th>
                                <th class="border border-gray-300 px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $index => $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.lrn" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.grade" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.section" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.last_name" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.first_name" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.sex" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.birthdate" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.age_years" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="text" wire:model.live="rows.{{ $index }}.age_months" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="number" step="0.01" min="0" wire:model.live="rows.{{ $index }}.weight" class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <input type="number" step="0.01" min="0" wire:model.live="rows.{{ $index }}.height" class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-700">
                                        {{ !empty($row['ip']) ? 'Yes' : 'No' }}
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <input type="checkbox" wire:model.live="rows.{{ $index }}._4ps" class="rounded border-gray-400 text-blue-600">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <input type="checkbox" wire:model.live="rows.{{ $index }}.pardo" class="rounded border-gray-400 text-blue-600">
                                    </td>
                                    <td class="border border-gray-300 py-2 text-center">
                                        <input type="checkbox" wire:model.live="rows.{{ $index }}.dewormed" class="rounded border-gray-400 text-blue-600">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <input type="checkbox" wire:model.live="rows.{{ $index }}.sbfp_previous_beneficiary" class="rounded border-gray-400 text-blue-600">
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <button wire:click="deleteRow({{ $index }})" wire:confirm="Remove this row?" type="button"
                                            class="inline-flex items-center justify-center w-6 h-6 rounded text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-600 mt-4">Edit any cells above as needed. Changes are saved automatically. Click "Save" to import the edited data. Select a different file to reload.</p>
            </div>
        @endif
    </div>
</div>
