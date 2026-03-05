<div class="space-y-4">
    <div class="bg-white shadow rounded p-4">
        <h2 class="text-lg font-semibold mb-3">Enrollees by Grade</h2>
        <div class="grid grid-cols-4 gap-3">
            @foreach($gradeCounts as $grade => $count)
                <div wire:click="loadSectionCounts('{{ $grade }}')" tabindex="0"
                    class="cursor-pointer flex items-center justify-between border rounded px-3 py-2 hover:shadow {{ $selectedGrade === (string) $grade ? 'bg-blue-50 border-blue-300 ring-1 ring-blue-200' : '' }}">
                    <div class="text-sm text-gray-600">@if($grade === 'k') Kinder @else Grade {{ $grade }} @endif</div>
                    <div class="text-lg font-medium text-gray-800">{{ $count }}</div>
                </div>

                @if($selectedGrade === (string) $grade)
                    <div class="col-span-4 mt-2">
                        <div class="border rounded p-3 bg-blue-50">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-medium">Sections for @if($selectedGrade === 'k') Kinder @else Grade {{ $selectedGrade }} @endif</div>
                                <div class="flex items-center gap-2">
                                    <button wire:click="clearStudents" class="text-sm text-gray-600">Clear Students</button>
                                    <button wire:click="clearSectionCounts" class="text-sm text-blue-600">Close</button>
                                </div>
                            </div>

                            <div class="space-y-2">
                                @forelse($sectionCounts as $entry)
                                    <div>
                                        <div class="flex items-center justify-between border rounded p-2">
                                            <div>
                                                <div class="text-sm text-gray-600">{{ $entry['label'] }}</div>
                                                <div class="text-xs text-gray-500">Students: {{ $entry['count'] }}</div>
                                            </div>
                                            <div>
                                                <button wire:click="loadStudents('{{ $entry['section'] }}')" class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Show</button>
                                            </div>
                                        </div>

                                        @if($selectedSection !== null && ((string)$entry['section'] === (string)$selectedSection || ($entry['section'] === null && $selectedSection === '')))
                                            <div class="mt-2 border rounded p-3 bg-white">
                                                <div class="text-sm font-medium mb-1">Students</div>
                                                @if(count($students))
                                                    <ul class="text-sm space-y-1 max-h-48 overflow-auto">
                                                        @foreach($students as $stu)
                                                            <li class="flex justify-between items-start space-x-3">
                                                                <div class="flex-1">
                                                                    <div class="font-medium">{{ $stu['full_name'] ?? ($stu['last_name'].' '.($stu['first_name'] ?? '')) }}</div>
                                                                    <div class="text-sm text-gray-600">Section: {{ $stu['section'] ?? '—' }} &middot; Height: {{ $stu['height'] ?? '—' }}m &middot; Weight: {{ $stu['weight'] ?? '—' }}kg</div>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    @if($editingStudent === $stu['id'])
                                                                        <div class="space-x-2">
                                                                            <span>Height (cm): </span>
                                                                            <input type="text" wire:model.defer="editingHeight" class="w-20 px-2 py-1 border rounded text-sm" placeholder="cm">
                                                                            <span>Weight : </span>
                                                                            <input type="text" wire:model.defer="editingWeight" class="w-20 px-2 py-1 border rounded text-sm" placeholder="kg">
                                                                            <button wire:click="saveEdit" class="ml-2 px-2 py-1 bg-green-600 text-white rounded text-xs">Save</button>
                                                                            <button wire:click="cancelEdit" class="ml-1 px-2 py-1 bg-gray-200 text-xs rounded">Cancel</button>
                                                                        </div>
                                                                    @else
                                                                        <button wire:click="startEdit({{ $stu['id'] }})" class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Edit</button>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="text-sm text-gray-500">No students in this section.</div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No sections found for this grade.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>