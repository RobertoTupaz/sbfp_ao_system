<div>
	<div class="flex items-center gap-2 mb-4">
		<input id="beneficiary-search" type="text" placeholder="Search beneficiaries by name, grade or section"
			wire:model.defer="search"
			class="mt-1 block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" />
		<button type="button" wire:click="searchBeneficiaries"
			class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-md shadow-sm hover:bg-blue-700 focus:outline-none">
			Search
		</button>
		<button type="button" wire:click="clearSearch"
			class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-50 focus:outline-none">
			Clear
		</button>
	</div>
	@if($setBeneficiaries && $beneficiaries->count())
		<div class="space-y-3">
			@foreach($beneficiaries as $beneficiary)
				<div
					class="flex items-center justify-between bg-white shadow-sm rounded-lg p-3 sm:p-4 hover:shadow-md transition-shadow">
					<div class="flex items-center space-x-3">
						<div
							class="flex-shrink-0 h-12 w-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold text-lg">
							{{ strtoupper(substr($beneficiary->full_name ?? '', 0, 1)) }}
						</div>
						<div>
							<div class="text-sm font-medium text-gray-900">{{ $beneficiary->full_name }}</div>
							@if(isset($beneficiary->grade))
								<div class="text-xs text-gray-500">Grade {{ $beneficiary->grade }}</div>
							@endif
						</div>
					</div>

					<div class="flex items-center space-x-2">
						<button type="button"
							class="inline-flex items-center px-3 py-2 bg-gradient-to-b from-blue-600 to-blue-500 text-white text-sm rounded-md shadow-sm hover:from-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400"
							aria-label="Edit {{ $beneficiary->full_name }}" onclick="toggleEditForm({{ $beneficiary->id }})">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
								stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
									d="M11 5h6m2 2v12a2 2 0 0 1-2 2H7l-4 0V7a2 2 0 0 1 2-2h8z" />
							</svg>
							Edit
						</button>

						<button type="button"
							class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-300"
							aria-label="Swap pupil {{ $beneficiary->full_name }}"
							wire:click="openSwapModal({{ $beneficiary->id }})">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20"
								fill="currentColor">
								<path fill-rule="evenodd"
									d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
									clip-rule="evenodd" />
							</svg>
							Swap Pupil
						</button>
					</div>
				</div>

				<div id="edit-form-{{ $beneficiary->id }}" class="hidden mt-3">
					<livewire:edit-beneficiaries.edit-pupil :pupil="$beneficiary" :key="'pupil-' . $beneficiary->id" />
				</div>
			@endforeach
		</div>
	@elseif($setBeneficiaries)
		<div class="text-center text-sm text-gray-500 py-6">No beneficiaries found.</div>
	@endif

	@if($showSwapModal)
		<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
			<div class="bg-white rounded-lg w-full max-w-3xl p-6 shadow-lg" style="max-height:70vh; overflow:auto;">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-lg font-medium">Select replacement pupil</h3>
					<button type="button" class="text-gray-600" wire:click="closeSwapModal">✕</button>
				</div>

				<div class="flex items-center gap-2 mb-4">
					<input type="text" wire:model.defer="swapSearch" placeholder="Search name, grade or section"
						class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" />
					<button type="button" wire:click="searchSwapCandidates"
						class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Search</button>
					<button type="button" wire:click="clearSwapSearch"
						class="px-3 py-2 bg-white border rounded text-sm">Clear</button>
				</div>

				<div class="overflow-auto border rounded">
					<table class="min-w-full text-sm">
						<thead class="bg-gray-50 sticky top-0">
							<tr>
								<th class="px-3 py-2 text-left">#</th>
								<th class="px-3 py-2 text-left">Name</th>
								<th class="px-3 py-2 text-left">Grade</th>
								<th class="px-3 py-2 text-left">Section</th>
								<th class="px-3 py-2 text-left">Action</th>
							</tr>
						</thead>
						<tbody>
							@forelse($swapCandidates as $i => $cand)
								<tr class="border-t">
									<td class="px-3 py-2 align-top">{{ $i + 1 }}</td>
									<td class="px-3 py-2">{{ $cand->full_name }}</td>
									<td class="px-3 py-2">{{ $cand->grade }}</td>
									<td class="px-3 py-2">{{ $cand->section }}</td>
									<td class="px-3 py-2">
										<div class="flex items-center space-x-2">
											<button wire:click.prevent="$set('swapSelectedTo', {{ $cand->id }})"
												class="px-2 py-1 bg-blue-600 text-white rounded text-xs">Select</button>

											@if($swapSelectedTo == $cand->id)
												<input wire:model.defer="swapReason" type="text" placeholder="Reason"
													class="px-2 py-1 border rounded text-sm" />
												<button wire:click="applySwap({{ $cand->id }})"
													class="px-2 py-1 bg-green-600 text-white rounded text-xs">Save</button>
											@endif
										</div>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="px-3 py-4 text-center text-gray-500">No pupils found.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="mt-4">
					<button type="button" wire:click="closeSwapModal"
						class="w-full px-3 py-2 bg-white text-red-600 border rounded">Close</button>
				</div>
			</div>
		</div>
	@endif
</div>

<script>
	function toggleEditForm(id) {
		try {
			var all = document.querySelectorAll('[id^="edit-form-"]');
			all.forEach(function (el) {
				if (el.id !== 'edit-form-' + id) el.classList.add('hidden');
			});
			var target = document.getElementById('edit-form-' + id);
			if (!target) return;
			if (target.classList.contains('hidden')) {
				target.classList.remove('hidden');
				setTimeout(function () { target.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 50);
			} else {
				target.classList.add('hidden');
			}
		} catch (err) {
			console.error('toggleEditForm error', err);
		}
	}
</script>

<script>
	window.addEventListener('swapped-success', function (e) {
		var msg = (e && e.detail && e.detail.message) ? e.detail.message : 'Swap completed';
		if (typeof Swal !== 'undefined') {
			Swal.fire({
				icon: 'success',
				title: 'Success',
				text: msg,
				timer: 2500,
				showConfirmButton: false
			});
		} else {
			alert(msg);
		}
	});
</script>