<div class="space-y-5 p-5">
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div>
        <h3 class="text-lg font-semibold text-gray-800">Deleted pupils</h3>
        <p class="mt-1 text-sm text-gray-500">
            Deleted pupil records and their nutritional data are retained here until restored.
        </p>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search pupil or section..."
            class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 sm:max-w-sm">

        <select
            wire:model.live="grade"
            class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="">All grades</option>
            <option value="k">Kinder</option>
            @foreach(range(1, 12) as $gradeOption)
                <option value="{{ $gradeOption }}">Grade {{ $gradeOption }}</option>
            @endforeach
            <option value="non_graded">Non-graded</option>
        </select>

        @if(trim($search) !== '' || $grade !== '')
            <button type="button" wire:click="clearFilters" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                Clear filters
            </button>
        @endif
    </div>

    @if($deletedPupils->count())
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-4 py-3">Pupil</th>
                        <th class="px-4 py-3">Grade / Section</th>
                        <th class="px-4 py-3">Sex / Birthday</th>
                        <th class="px-4 py-3">Height / Weight</th>
                        <th class="px-4 py-3">BMI / Status</th>
                        <th class="px-4 py-3">Height-for-age</th>
                        <th class="px-4 py-3">Deleted</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($deletedPupils as $pupil)
                        <tr wire:key="deleted-pupil-{{ $pupil->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $pupil->full_name ?: trim($pupil->last_name.', '.$pupil->first_name.' '.$pupil->suffix_name) }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $pupil->grade === 'k' ? 'Kinder' : ($pupil->grade === 'non_graded' ? 'Non-graded' : 'Grade '.$pupil->grade) }}
                                <div class="text-xs text-gray-400">{{ $pupil->section ?: 'Unspecified section' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $pupil->sex ?: '—' }}
                                <div class="text-xs text-gray-400">{{ $pupil->birthday?->format('M d, Y') ?: 'No birthday' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $pupil->height !== null ? number_format($pupil->height, 1).' cm' : '—' }}
                                <div class="text-xs text-gray-400">{{ $pupil->weight !== null ? number_format($pupil->weight, 1).' kg' : '—' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $pupil->bmi !== null ? number_format($pupil->bmi, 1) : '—' }}
                                <div class="text-xs text-gray-400">{{ $pupil->nutritional_status ?: 'No status' }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $pupil->height_for_age ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $pupil->deleted_at?->format('M d, Y g:i A') }}
                                <div class="text-xs text-gray-400">by {{ $pupil->deletedBy?->name ?: 'Unknown user' }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="restorePupil({{ $pupil->id }})"
                                    wire:confirm="Restore this pupil to Track Enrollees?"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                                    Restore
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $deletedPupils->links() }}
        </div>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 px-6 py-12 text-center text-sm text-gray-500">
            No deleted pupils found.
        </div>
    @endif
</div>
