<div class="max-w-5xl mx-auto px-4">
    {{-- <h3 class="text-2xl font-semibold text-gray-800 mb-6">Generate JSON</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div id="generateJSON" class="group block bg-white rounded-lg shadow-md p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <div wire:loading wire:target="generateJson" class="flex items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="generateJson">
                    <svg class="w-12 h-12 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4v4m0 8v4M4 12h4m8 0h4M6.3 6.3l2.8 2.8m6.6 6.6l2.8 2.8M6.3 17.7l2.8-2.8m6.6-6.6l2.8-2.8" />
                    </svg>
                </div>
            </div>

            <div class="text-lg font-medium text-gray-800">
                <a href="#" role="button" aria-label="Export JSON" wire:click.prevent="generateJson"
                    wire:loading.attr="disabled" wire:target="generateJson"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded">
                    <span wire:loading.remove wire:target="generateJson">Export JSON</span>
                    <span wire:loading wire:target="generateJson">Preparing...</span>
                </a>
            </div>
            <div class="text-sm text-gray-500 mt-2">Export `nutritional_statuses` table as JSON file</div>
        </div>
        <div id="uploadJSON" class="group block bg-white rounded-lg shadow-md p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <div wire:loading wire:target="importJson" class="flex items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="importJson">
                    <svg class="w-12 h-12 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </div>
            </div>

            <div class="text-lg font-medium text-gray-800">
                <div class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded">
                    <input type="file" wire:model="uploadJson" accept=".json" class="mb-3 w-full" />
                    <button type="button" aria-label="Upload JSON" wire:click.prevent="importJson"
                        wire:loading.attr="disabled" wire:target="importJson"
                        class="w-full px-3 py-2 text-sm text-gray-700 bg-white border rounded">Upload JSON</button>
                </div>
            </div>
            <div class="text-sm text-gray-500 mt-2">Upload a JSON export to import into `nutritional_statuses` table</div>
        </div>
    </div> --}}
    <h3 class="text-2xl font-semibold text-gray-800 mb-6">Generate Forms</h3>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiaries count</label>
        <div class="flex gap-2 items-center max-w-sm">
            <input type="number" min="0" wire:model.defer="beneficiariesCount"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md px-3 py-2" />
            <button type="button" wire:click.prevent="saveBeneficiariesCount" wire:loading.attr="disabled"
                class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </div>
        <div class="text-sm text-gray-500 mt-2">Set number of beneficiaries for the generated forms</div>
    </div>

    <div wire:click.prevent="generateSnsElem" class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <a href="#" role="button" aria-label="Generate SNS Elementary Form"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-indigo-600 group-hover:text-indigo-700" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                </svg>
            </div>
            <div class="text-lg font-medium text-gray-800">SNS Elementary</div>
            <div class="text-sm text-gray-500 mt-2">Generate SNS Elementary Form</div>
        </a>

        <a href="#" role="button" aria-label="Generate SNS High School Form"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-cyan-600 group-hover:text-cyan-700" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                </svg>
            </div>
            <div class="text-lg font-medium text-gray-800">SNS High School</div>
            <div class="text-sm text-gray-500 mt-2">Generate SNS High School Form</div>
        </a>

        <a href="#" role="button" aria-label="Generate Form 1" wire:click.prevent="generateForm1"
            wire:loading.attr="disabled" wire:target="generateForm1"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
            <div class="flex items-center justify-center mb-4">
                <div wire:loading wire:target="generateForm1" class="flex items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="generateForm1">
                    <svg class="w-12 h-12 text-blue-600 group-hover:text-blue-700" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                    </svg>
                </div>
            </div>
            <div class="text-lg font-medium text-gray-800">
                <span wire:loading.remove wire:target="generateForm1">Form 1</span>
                <span wire:loading wire:target="generateForm1">Generating...</span>
            </div>
            <div class="text-sm text-gray-500 mt-2">
                <span wire:loading.remove wire:target="generateForm1">Generate Form 1 (Pupils / Nutritional
                    Status)</span>
                <span wire:loading wire:target="generateForm1">Please wait — preparing download</span>
            </div>
        </a>

        <a href="#" role="button" aria-label="Generate Form 2"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-green-600 group-hover:text-green-700" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                </svg>
            </div>
            <div class="text-lg font-medium text-gray-800">Form 2</div>
            <div class="text-sm text-gray-500 mt-2">Generate Form 2 (School Summary)</div>
        </a>

        <a href="#" role="button" aria-label="Generate Form 3"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-purple-600 group-hover:text-purple-700" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                </svg>
            </div>
            <div class="text-lg font-medium text-gray-800">Form 5</div>
            <div class="text-sm text-gray-500 mt-2">Generate Form 5 (Milk Beneficiaries)</div>
        </a>
    </div>
</div>
</div>

<script>
    (function () {
        window.addEventListener('form1-ready', function (e) {
            try {
                var url = e && e.detail && e.detail.url ? e.detail.url : null;
                // Livewire v3 dispatch sends named params under detail by default
                if (!url && e && e.detail) url = e.detail; // fallback
                if (!url) return;
                // trigger download
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

        window.addEventListener('json-ready', function (e) {
            try {
                var url = e && e.detail && e.detail.url ? e.detail.url : null;
                if (!url && e && e.detail) url = e.detail;
                if (!url) return;
                var a = document.createElement('a');
                a.href = url;
                a.download = '';
                document.body.appendChild(a);
                a.click();
                a.remove();
            } catch (err) {
                console.error('JSON download error', err);
            }
        });

        window.addEventListener('beneficiaries-saved', function (e) {
            try {
                var detail = e && e.detail ? e.detail : null;
                var count = null;
                if (detail && typeof detail === 'object' && 'count' in detail) count = detail.count;
                if (!count && detail && typeof detail !== 'object') count = detail;
                var msg = 'Beneficiaries count saved' + (count !== null ? ': ' + count : '.');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Saved',
                        text: msg,
                        icon: 'success',
                        timer: 2500,
                        showConfirmButton: false
                    });
                } else {
                    alert(msg);
                }
            } catch (err) {
                console.error('SweetAlert error', err);
            }
        });
    })();
</script>