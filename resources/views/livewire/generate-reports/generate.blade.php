<div class="space-y-4">
	<div class="flex items-center justify-start space-x-4">
		<div class="w-full max-w-xs">
			<h2 class="text-xl font-semibold text-gray-800">School Year</h2>
			<select id="school_year" wire:model="selectedYear"
				class="w-full px-3 py-2 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
				<option value="" @if((string)session('focal_selected_year', '') == '') selected @endif>All school years</option>
				@foreach($schoolYears as $year)
					<option value="{{ $year }}" @if((string)session('focal_selected_year', '') == (string)$year) selected @endif>{{ $year }}</option>
				@endforeach
			</select>
		</div>

		<div class="w-full max-w-xs">
			<h2 class="text-xl font-semibold text-gray-800">State</h2>
			<select id="survey_state_global" wire:model="selectedStateGlobal"
				class="w-full px-3 py-2 border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
				<option value="" @if((string)session('focal_selected_state', '') == '') selected @endif>Select state (applies to all)</option>
				@foreach($states as $st)
					<option value="{{ $st }}" @if((string)session('focal_selected_state', '') == (string)$st) selected @endif>{{ ucfirst($st) }}</option>
				@endforeach
			</select>
		</div>

				<div class="flex items-end">
			<button wire:click="saveSelections" type="button"
				class="ml-2 px-4 py-2 bg-blue-600 text-white rounded shadow-sm text-sm">Save</button>
		</div>
	</div>
	<h2 class="text-xl font-semibold text-gray-800">Generate Reports</h2>
	<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
		<a href="#" role="button" aria-label="Generate Baseline" wire:click.prevent="generateBaseline"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateBaseline" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateBaseline">
					<svg class="w-12 h-12 text-blue-600 group-hover:text-blue-700" xmlns="http://www.w3.org/2000/svg"
						fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateBaseline">Generate Baseline</span>
				<span wire:loading wire:target="generateBaseline">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Baseline report</div>
		</a>
		<a href="#" role="button" aria-label="Generate Midline" wire:click.prevent="generateMidline"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateMidline" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-pink-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateMidline">
					<svg class="w-12 h-12 text-pink-600 group-hover:text-pink-700" xmlns="http://www.w3.org/2000/svg"
						fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateMidline">Generate Midline</span>
				<span wire:loading wire:target="generateMidline">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Midline report</div>
		</a>
		<a href="#" role="button" aria-label="Generate Form 7" wire:click.prevent="generateForm7"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateForm7" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateForm7">
					<svg class="w-12 h-12 text-indigo-600 group-hover:text-indigo-700"
						xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateForm7">Generate Form 7</span>
				<span wire:loading wire:target="generateForm7">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Form 7</div>
		</a>

		<a href="#" role="button" aria-label="Generate Form 2" wire:click.prevent="generateForm2"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateForm2" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-cyan-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateForm2">
					<svg class="w-12 h-12 text-cyan-600 group-hover:text-cyan-700" xmlns="http://www.w3.org/2000/svg"
						fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateForm2">Generate Form 2</span>
				<span wire:loading wire:target="generateForm2">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Form 2 (School Summary)</div>
		</a>

		{{-- <a href="#" role="button" aria-label="Generate Consolidated Form 7"
			wire:click.prevent="generateConsolidatedForm7"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateConsolidatedForm7" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateConsolidatedForm7">
					<svg class="w-12 h-12 text-emerald-600 group-hover:text-emerald-700"
						xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateConsolidatedForm7">Generate Consolidated Form 7</span>
				<span wire:loading wire:target="generateConsolidatedForm7">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Consolidated Form 7</div>
		</a> --}}
		{{--
		<a href="#" role="button" aria-label="Generate Consolidated Form 2"
			wire:click.prevent="generateConsolidatedForm2"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateConsolidatedForm2" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateConsolidatedForm2">
					<svg class="w-12 h-12 text-green-600 group-hover:text-green-700" xmlns="http://www.w3.org/2000/svg"
						fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateConsolidatedForm2">Generate Consolidated Form 2</span>
				<span wire:loading wire:target="generateConsolidatedForm2">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Consolidated Form 2</div>
		</a> --}}

		{{-- <a href="#" role="button" aria-label="Generate Consolidated Baseline"
			wire:click.prevent="generateConsolidatedBaseline"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateConsolidatedBaseline" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateConsolidatedBaseline">
					<svg class="w-12 h-12 text-purple-600 group-hover:text-purple-700"
						xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateConsolidatedBaseline">Generate Consolidated
					Baseline</span>
				<span wire:loading wire:target="generateConsolidatedBaseline">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Consolidated Baseline</div>
		</a> --}}


		{{--
		<a href="#" role="button" aria-label="Generate Consolidated Midline"
			wire:click.prevent="generateConsolidatedMidline"
			class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
			<div class="flex items-center justify-center mb-4">
				<div wire:loading wire:target="generateConsolidatedMidline" class="flex items-center justify-center">
					<svg class="animate-spin h-12 w-12 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none"
						viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
						</circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
					</svg>
				</div>
				<div wire:loading.remove wire:target="generateConsolidatedMidline">
					<svg class="w-12 h-12 text-yellow-600 group-hover:text-yellow-700"
						xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
							d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
					</svg>
				</div>
			</div>
			<div class="text-lg font-medium text-gray-800">
				<span wire:loading.remove wire:target="generateConsolidatedMidline">Generate Consolidated Midline</span>
				<span wire:loading wire:target="generateConsolidatedMidline">Generating...</span>
			</div>
			<div class="text-sm text-gray-500 mt-2">Generate Consolidated Midline</div>
		</a> --}}
	</div>
</div>