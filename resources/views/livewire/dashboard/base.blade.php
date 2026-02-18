<div class="max-w-xl mx-auto">
	@if($showSchoolCard && $school_id)
		<div id="schoolCard" class="bg-white rounded-xl shadow-md p-6 mb-6">
			<h2 class="text-lg font-semibold mb-4">{{ optional(collect($schools)->firstWhere('id', $school_id))->name }}
			</h2>
			<div class="flex flex-row gap-4 mt-4">
				<div class="flex flex-col items-center bg-green-50 border border-green-200 rounded-lg p-4 w-1/2 shadow">
					<div class="font-semibold text-green-700 mb-2">Form 2 (NFP)</div>
					<button type="button"
						class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">Generate</button>
				</div>
				<div class="flex flex-col items-center bg-blue-50 border border-blue-200 rounded-lg p-4 w-1/2 shadow">
					<div class="font-semibold text-blue-700 mb-2">Form 2 (Milk)</div>
					<button type="button"
						class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">Generate</button>
				</div>
			</div>
		</div>
	@endif

	{{-- <div class="bg-white rounded-xl shadow-md p-6 mb-6">
		<h2 class="text-lg font-semibold mb-4">Select District & Upload Excel</h2>
		<form wire:submit.prevent="uploadExcel">
			<div class="mb-4">
				<label for="district" class="block text-sm font-medium text-gray-700 mb-1">District</label>
				<select id="district" wire:model="district_id" wire:change="userDistrictSelection($event.target.value)"
					class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
					<option value="">-- Select District --</option>
					@foreach($districts as $district)
						<option value="{{ $district->id }}">{{ $district->name }}</option>
					@endforeach
				</select>
			</div>

			@if($district_id && count($schools) > 0)
			<div class="mb-4">
				<label for="school" class="block text-sm font-medium text-gray-700 mb-1">School</label>
				<select id="school" wire:model="school_id" wire:change="userSchoolSelection($event.target.value)"
					class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
					<option value="">-- Select School --</option>
					@foreach($schools as $school)
						<option value="{{ $school->id }}">{{ $school->name }}</option>
					@endforeach
				</select>
			</div>
			@endif
			@if($school_id >= 1)
			<div class="mt-6 space-y-4">
				<div class="flex flex-col gap-2">
					<label for="nfp" class="text-sm font-semibold text-gray-800">📊 NFP Excel File</label>
					<div class="relative">
						<input type="file" id="nfp" wire:model="nfp_file" class="hidden" accept=".xlsx,.xls">
						<label for="nfp"
							class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-blue-300 rounded-lg bg-blue-50 hover:bg-blue-100 hover:border-blue-400 cursor-pointer transition duration-200">
							<span class="text-blue-600 font-medium text-sm">
								@if($nfp_file)
								✅ {{ $nfp_file->getClientOriginalName() }}
								@else
								Choose NFP file or drag here
								@endif
							</span>
						</label>
					</div>
				</div>

				<div class="flex flex-col gap-2">
					<label for="milk" class="text-sm font-semibold text-gray-800">🥛 Milk Excel File</label>
					<div class="relative">
						<input type="file" id="milk" wire:model="milk_file" class="hidden" accept=".xlsx,.xls">
						<label for="milk"
							class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-amber-300 rounded-lg bg-amber-50 hover:bg-amber-100 hover:border-amber-400 cursor-pointer transition duration-200">
							<span class="text-amber-600 font-medium text-sm">
								@if($milk_file)
								✅ {{ $milk_file->getClientOriginalName() }}
								@else
								Choose Milk file or drag here
								@endif
							</span>
						</label>
					</div>
				</div>
			</div>

			<button type="submit"
				class="mt-6 w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
				<span>⬆️ Upload Excel Files</span>
			</button>
			@endif
		</form>
	</div> --}}
</div>