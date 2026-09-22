<div class="space-y-4">
	<div class="flex items-center justify-start space-x-4">
		<div class="w-full max-w-xs">
			<h2 class="text-xl font-semibold text-gray-800">School Year</h2>
			<select id="school_year" wire:model="selectedYear"
				class="w-full px-3 py-2 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
				<option value="">All school years</option>
				@foreach($schoolYears as $year)
					<option value="{{ $year }}">{{ $year }}</option>
				@endforeach
			</select>
		</div>

		<div class="w-full max-w-xs">
			<h2 class="text-xl font-semibold text-gray-800">State</h2>
			<select id="survey_state_global" wire:model="selectedStateGlobal"
				class="w-full px-3 py-2 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
				<option value="">Select state (applies to all)</option>
				@foreach($states as $st)
					<option value="{{ $st }}">{{ ucfirst($st) }}</option>
				@endforeach
			</select>
		</div>

		<div class="flex items-end">
			<button wire:click="saveSelections" type="button"
				class="ml-2 px-4 py-2 bg-blue-600 text-white rounded shadow-sm text-sm">Save</button>
		</div>
	</div>
	<div class="flex items-center justify-between">
		<h2 class="text-xl font-semibold text-gray-800">Schools by District <span
				class="ml-2 text-sm font-medium text-gray-600">({{ $selectedStateGlobal ? ucfirst($selectedStateGlobal) : 'None selected' }})</span>
		</h2>
		<div class="w-full max-w-sm">
			<label for="search" class="sr-only">Search</label>
			<input id="search" wire:model.debounce.300ms="search" type="text"
				placeholder="Search schools or districts..."
				class="w-full px-3 py-2 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-200" />
		</div>
	</div>

	@if(empty($schoolsByDistrict))
		<div class="text-center py-12 bg-white border border-dashed rounded-lg">
			<svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
				xmlns="http://www.w3.org/2000/svg">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					d="M3 7v4a1 1 0 001 1h3m10-6h3a1 1 0 011 1v4m-6 4v6m-6-6v6"></path>
			</svg>
			<p class="mt-4 text-sm text-gray-600">No schools found.</p>
		</div>
	@else
		<div class="grid gap-4 grid-cols-1 sm:grid-cols-1 lg:grid-cols-2">
			@foreach($schoolsByDistrict as $district => $schools)
				<div class="bg-white border rounded-lg p-4 shadow-sm hover:shadow-md transition">
					<div class="flex items-center justify-between mb-3">
						<h3 class="text-lg font-medium text-gray-800">District {{ $district ?: 'Unassigned District' }}</h3>
						<span
							class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ count($schools) }}</span>
					</div>

					<ul class="space-y-2">
						@foreach($schools as $school)
							@include('livewire.dashboard.focal.school-row', [
								'school' => $school,
								'schoolsWithData' => $schoolsWithData,
								'selectedState' => $selectedState,
								'selectedStateGlobal' => $selectedStateGlobal,
								'uploads' => $uploads,
							])
						@endforeach
					</ul>
				</div>
			@endforeach
		</div>
	@endif
</div>
<script>
	(function () {
		if (window._focal_upload_listeners_added) return;
		window._focal_upload_listeners_added = true;

		window.focalHandleUploadChange = function (input, saveId) {
			try {
				var btn = document.getElementById(saveId);
				if (!btn) return;
				if (input && input.files && input.files.length > 0) {
					btn.classList.remove('hidden');
				} else {
					btn.classList.add('hidden');
				}
			} catch (err) {
				console.error('focal upload change handler error', err);
			}
		};
	})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	if (!window._focal_swal_listeners_added) {
		window._focal_swal_listeners_added = true;

		window.addEventListener('focal-upload-saved', function (e) {
			var school = (e && e.detail && e.detail.school) ? e.detail.school : null;
			Swal.fire({
				icon: 'success',
				title: 'Upload saved',
				text: school ? 'Form 1 saved for school ID ' + school : 'Form 1 saved successfully',
				timer: 2500,
				showConfirmButton: false
			});
		});

		window.addEventListener('focal-selection-saved', function (e) {
			var state = (e && e.detail && e.detail.state) ? e.detail.state : null;
			var year = (e && e.detail && e.detail.year) ? e.detail.year : null;
			Swal.fire({
				icon: 'success',
				title: 'Selections saved',
				text: (state ? ('State: ' + state + (year ? (', Year: ' + year) : '')) : (year ? ('Year: ' + year) : 'Selections saved')),
				timer: 2000,
				showConfirmButton: false
			});
		});

		window.addEventListener('focal-upload-error', function (e) {
			var msg = (e && e.detail && e.detail.message) ? e.detail.message : 'An error occurred while saving the upload.';
			var school = (e && e.detail && e.detail.school) ? e.detail.school : null;
			Swal.fire({
				icon: 'error',
				title: 'Upload failed',
				text: (school ? ('School ID ' + school + ': ') : '') + msg,
				showConfirmButton: true
			});
		});

		// trigger download when baseline file is ready
		window.addEventListener('focal-baseline-ready', function (e) {
			try {
				var url = e && e.detail && e.detail.url ? e.detail.url : null;
				// Livewire v3 dispatch sends named params under detail by default
				if (!url && e && e.detail) url = e.detail; // fallback
				if (!url) return;
				var a = document.createElement('a');
				a.href = url;
				a.download = '';
				document.body.appendChild(a);
				a.click();
				a.remove();
			} catch (err) {
				console.error('Download error', err);
			}
		});
	}
</script>