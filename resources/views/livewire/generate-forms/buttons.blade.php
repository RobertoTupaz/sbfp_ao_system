<div class="max-w-5xl mx-auto px-4">
    <h3 class="text-2xl font-semibold text-gray-800 mb-6">Generate Forms</h3>

    {{-- <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Beneficiaries count</label>
        <div class="flex gap-2 items-center max-w-sm">
            <input type="number" min="0" wire:model.defer="beneficiariesCount"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md px-3 py-2" />
            <button type="button" wire:click.prevent="saveBeneficiariesCount" wire:loading.attr="disabled"
                class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
        </div>
        <div class="text-sm text-gray-500 mt-2">Set number of beneficiaries for the generated forms</div>
    </div> --}}

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <a href="#" role="button" aria-label="Generate SNS Elementary Form" wire:click.prevent="generateSnsElem"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
            <div class="flex items-center justify-center mb-4">
                <div wire:loading wire:target="generateSnsElem" class="flex items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="generateSnsElem">
                    <svg class="w-12 h-12 text-indigo-600 group-hover:text-indigo-700" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                    </svg>
                </div>
            </div>
            <div class="text-lg font-medium text-gray-800">
                <span wire:loading.remove wire:target="generateSnsElem">SNS Elementary</span>
                <span wire:loading wire:target="generateSnsElem">Generating...</span>
            </div>
            <div class="text-sm text-gray-500 mt-2">
                <span wire:loading.remove wire:target="generateSnsElem">Generate SNS Elementary Form</span>
                <span wire:loading wire:target="generateSnsElem">Please wait — preparing download</span>
            </div>
        </a>

        <a wire:click.prevent="generateSnsHighSchool" role="button" aria-label="Generate SNS High School Form"
            class="group block bg-white rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-1 transition p-6 text-center cursor-pointer">
            <div class="flex items-center justify-center mb-4">
                <div wire:loading wire:target="generateSnsHighSchool" class="flex items-center justify-center">
                    <svg class="animate-spin h-12 w-12 text-cyan-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                </div>
                <div wire:loading.remove wire:target="generateSnsHighSchool">
                    <svg class="w-12 h-12 text-cyan-600 group-hover:text-cyan-700" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6M7 8h10M5 6h14v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6z" />
                    </svg>
                </div>
            </div>
            <div class="text-lg font-medium text-gray-800">
                <span wire:loading.remove wire:target="generateSnsHighSchool">SNS High School</span>
                <span wire:loading wire:target="generateSnsHighSchool">Generating...</span>
            </div>
            <div class="text-sm text-gray-500 mt-2">
                <span wire:loading.remove wire:target="generateSnsHighSchool">Generate SNS High School Form</span>
                <span wire:loading wire:target="generateSnsHighSchool">Please wait — preparing download</span>
            </div>
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

        {{-- <a href="#" role="button" aria-label="Generate Form 2"
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
        </a> --}}

        {{-- <a href="#" role="button" aria-label="Generate Form 3"
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
        </a> --}}
    </div>
</div>
</div>

<script>
    (function () {
        // prevent adding listeners multiple times when the component is re-rendered / navigated
        if (window._generateForms_buttons_listeners_added) return;
        window._generateForms_buttons_listeners_added = true;

        // Unified download handler with simple de-duplication
        window._generateForms_buttons_last = window._generateForms_buttons_last || { url: null, time: 0 };
        function _gf_trigger_download(url) {
            try {
                if (!url) return;
                var now = Date.now();
                if (window._generateForms_buttons_last.url === url && (now - window._generateForms_buttons_last.time) < 3000) {
                    return; // ignore duplicate within 3s
                }
                window._generateForms_buttons_last.url = url;
                window._generateForms_buttons_last.time = now;
                var a = document.createElement('a');
                a.href = url;
                a.download = '';
                document.body.appendChild(a);
                a.click();
                a.remove();
            } catch (err) {
                console.error('Download error', err);
            }
        }

        window.addEventListener('form1-ready', function (e) {
            var url = e && e.detail && e.detail.url ? e.detail.url : null;
            if (!url && e && e.detail) url = e.detail;
            _gf_trigger_download(url);
        });

        // SNS exports previously dispatched with the wrong event name; listen for their corrected events too
        window.addEventListener('sns-elem-ready', function (e) {
            var url = e && e.detail && e.detail.url ? e.detail.url : null;
            if (!url && e && e.detail) url = e.detail;
            _gf_trigger_download(url);
        });

        window.addEventListener('sns-highschool-ready', function (e) {
            var url = e && e.detail && e.detail.url ? e.detail.url : null;
            if (!url && e && e.detail) url = e.detail;
            _gf_trigger_download(url);
        });

        window.addEventListener('json-ready', function (e) {
            try {
                var url = e && e.detail && e.detail.url ? e.detail.url : null;
                if (!url && e && e.detail) url = e.detail;
                if (!url) return;
                _gf_trigger_download(url);
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