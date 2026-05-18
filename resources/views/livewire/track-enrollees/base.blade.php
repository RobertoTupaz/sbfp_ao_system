<div class="space-y-5 p-1">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Grade selector --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-1.5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Select Grade</h2>
            <x-feature-help>Browse enrolled pupils by grade and section. Select a grade to see its sections, then pick a section to load the pupil list. Click Edit on any pupil to update their height and weight — BMI, nutritional status, and height-for-age are recalculated and saved automatically.</x-feature-help>
        </div>
            @if($selectedGrade)
                <button wire:click="clearSectionCounts" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </button>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($gradeCounts as $grade => $count)
                <button
                    wire:click="loadSectionCounts('{{ $grade }}')"
                    class="relative flex flex-col items-center px-4 py-2 rounded-lg border text-sm font-medium transition-all
                        {{ $selectedGrade === (string)$grade
                            ? 'bg-blue-600 border-blue-600 text-white shadow-md'
                            : 'bg-white border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50' }}">
                    <span>{{ $grade === 'k' ? 'Kinder' : ($grade === 'non_graded' ? 'Non-graded' : 'Grade '.$grade) }}</span>
                    <span class="text-xs mt-0.5 {{ $selectedGrade === (string)$grade ? 'text-blue-100' : 'text-gray-400' }}">
                        {{ $count['total'] }}
                    </span>
                    @if($count['no_hw'] > 0)
                        <span class="text-xs mt-0.5 {{ $selectedGrade === (string)$grade ? 'text-orange-200' : 'text-orange-500' }}">
                            {{ $count['no_hw'] }} no H&amp;W
                        </span>
                    @endif
                </button>
            @endforeach

            @if(auth()->user()->role === 'ao')
                <button
                    wire:click="$toggle('showDeleteAll')"
                    class="flex flex-col items-center px-4 py-2 rounded-lg border text-sm font-medium transition-all
                        {{ $showDeleteAll ? 'bg-gray-100 border-gray-300 text-gray-500' : 'bg-white border-gray-200 text-gray-500 hover:border-gray-400 hover:bg-gray-50' }}">
                    <span>{{ $showDeleteAll ? 'Hide' : 'Show Delete All' }}</span>
                    <span class="text-xs mt-0.5 text-gray-400">options</span>
                </button>

                @if($showDeleteAll)
                    <button
                        wire:click="deleteAllPupils"
                        wire:confirm="Delete ALL pupils from the database? This cannot be undone."
                        class="flex flex-col items-center px-4 py-2 rounded-lg border text-sm font-medium transition-all bg-red-600 border-red-600 text-white hover:bg-red-700">
                        <span>Delete All</span>
                        <span class="text-xs mt-0.5 text-red-200">pupils</span>
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- Section selector --}}
    @if($selectedGrade && count($sectionCounts))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">
                    Sections — {{ $selectedGrade === 'k' ? 'Kinder' : 'Grade '.$selectedGrade }}
                </h2>
                @if($selectedSection !== null)
                    <button wire:click="clearStudents" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear
                    </button>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($sectionCounts as $entry)
                    <button
                        wire:click="loadStudents('{{ $entry['section'] }}')"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition-all
                            {{ (string)$selectedSection === (string)$entry['section']
                                ? 'bg-indigo-600 border-indigo-600 text-white shadow-md'
                                : 'bg-white border-gray-200 text-gray-700 hover:border-indigo-300 hover:bg-indigo-50' }}">
                        <span>{{ $entry['label'] }}</span>
                        <span class="px-1.5 py-0.5 rounded-full text-xs
                            {{ (string)$selectedSection === (string)$entry['section'] ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-500' }}">
                            {{ $entry['count'] }}
                        </span>
                        @if($entry['no_hw'] > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-xs
                                {{ (string)$selectedSection === (string)$entry['section'] ? 'bg-orange-400 text-white' : 'bg-orange-100 text-orange-600' }}">
                                {{ $entry['no_hw'] }} no H&amp;W
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Student table --}}
    @if(count($students))
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">
                    {{ $selectedGrade === 'k' ? 'Kinder' : 'Grade '.$selectedGrade }}
                    @if($selectedSection) &mdash; {{ $selectedSection }} @endif
                    <span class="ml-2 text-sm font-normal text-gray-400">{{ count($students) }} pupils</span>
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                            <th class="px-4 py-3 w-8">#</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Sex</th>
                            <th class="px-4 py-3">Age</th>
                            <th class="px-4 py-3">Height (cm)</th>
                            <th class="px-4 py-3">Weight (kg)</th>
                            <th class="px-4 py-3">BMI</th>
                            <th class="px-4 py-3">Nutritional Status</th>
                            <th class="px-4 py-3">HFA</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($students as $i => $stu)
                            @if($editingStudent === $stu['id'])
                                {{-- Edit row --}}
                                <tr class="bg-blue-50" wire:key="edit-{{ $stu['id'] }}">
                                    <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        {{ $stu['full_name'] ?? trim(($stu['last_name'] ?? '').' '.($stu['first_name'] ?? '')) }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $stu['sex'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        @if($stu['age_years'] !== null)
                                            {{ $stu['age_years'] }}y
                                            @if($stu['age_months']) {{ $stu['age_months'] }}m @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1">
                                            <input
                                                type="number"
                                                wire:model.defer="editingHeight"
                                                step="0.1"
                                                min="0"
                                                placeholder="cm"
                                                class="w-24 px-3 py-1.5 border border-blue-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
                                                autofocus>
                                            <span class="text-xs text-gray-400">cm</span>
                                        </div>
                                        @error('editingHeight') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1">
                                            <input
                                                type="number"
                                                wire:model.defer="editingWeight"
                                                step="0.1"
                                                min="0"
                                                placeholder="kg"
                                                class="w-24 px-3 py-1.5 border border-blue-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                                            <span class="text-xs text-gray-400">kg</span>
                                        </div>
                                        @error('editingWeight') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 text-xs">recalc on save</td>
                                    <td class="px-4 py-3 text-gray-400 text-xs">recalc on save</td>
                                    <td class="px-4 py-3 text-gray-400 text-xs">recalc on save</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="saveEdit" wire:loading.attr="disabled"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                                <svg wire:loading wire:target="saveEdit" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                                Save
                                            </button>
                                            <button wire:click="cancelEdit"
                                                class="px-3 py-1.5 bg-white border border-gray-300 text-gray-600 text-xs font-medium rounded-lg hover:bg-gray-50">
                                                Cancel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                {{-- Normal row --}}
                                <tr class="hover:bg-gray-50 transition-colors" wire:key="row-{{ $stu['id'] }}">
                                    <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        {{ $stu['full_name'] ?? trim(($stu['last_name'] ?? '').' '.($stu['first_name'] ?? '')) }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $stu['sex'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        @if($stu['age_years'] !== null)
                                            {{ $stu['age_years'] }}y
                                            @if($stu['age_months']) {{ $stu['age_months'] }}m @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $stu['height'] !== null ? $stu['height'].' cm' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $stu['weight'] !== null ? $stu['weight'].' kg' : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">
                                        {{ $stu['bmi'] !== null ? number_format($stu['bmi'], 1) : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $ns = $stu['nutritional_status'] ?? null; @endphp
                                        @if($ns)
                                            @php
                                                $nsColor = match(strtolower($ns)) {
                                                    'severely wasted' => 'bg-red-100 text-red-700',
                                                    'wasted'          => 'bg-orange-100 text-orange-700',
                                                    'normal'          => 'bg-green-100 text-green-700',
                                                    'overweight'      => 'bg-yellow-100 text-yellow-700',
                                                    'obese'           => 'bg-amber-100 text-amber-700',
                                                    default           => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $nsColor }}">{{ ucfirst($ns) }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $hfa = $stu['height_for_age'] ?? null; @endphp
                                        @if($hfa)
                                            @php
                                                $hfaColor = match($hfa) {
                                                    'Severely Stunted' => 'bg-red-100 text-red-700',
                                                    'Stunted'          => 'bg-orange-100 text-orange-700',
                                                    'Normal'           => 'bg-green-100 text-green-700',
                                                    'Tall'             => 'bg-blue-100 text-blue-700',
                                                    default            => 'bg-gray-100 text-gray-600',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $hfaColor }}">{{ $hfa }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="startEdit({{ $stu['id'] }})"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-gray-300 text-gray-600 text-xs font-medium rounded-lg hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-colors">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </button>
                                            <button wire:click="deletePupil({{ $stu['id'] }})" wire:confirm="Delete this pupil? This cannot be undone."
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-red-200 text-red-500 text-xs font-medium rounded-lg hover:bg-red-50 hover:border-red-400 hover:text-red-700 transition-colors">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($selectedGrade && $selectedSection !== null)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm">No pupils found in this section.</p>
        </div>

    @elseif(!$selectedGrade)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-sm">Select a grade to get started.</p>
        </div>
    @endif

</div>
