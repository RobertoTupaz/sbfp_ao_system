<div class="mx-auto bg-white rounded-lg shadow p-6">
    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4 gap-4">
        <h3 class="text-lg font-semibold">Add Pupil</h3>

        <div class="flex flex-col items-center items-end gap-2">
            <label class="text-sm font-medium text-gray-700">Date of weighing</label>
            <input type="date" id="date_of_weighing" wire:model.defer="date_of_weighing"
                class="mt-1 block rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            @error('date_of_weighing')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <form wire:submit.prevent="savePupil">
        <div class="flex gap-4 items-start justify-center overflow-x-auto py-2">
            <div class="flex flex-col min-w-[220px]">
                <label class="text-sm font-medium text-gray-700">Name</label>
                <input id="name" type="text" wire:model.defer="name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[80px]">
                <label class="text-sm font-medium text-gray-700">Grade</label>
                <input id="grade" type="text" wire:model.defer="grade"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('grade')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Section</label>
                <input id="section" type="text" wire:model.defer="section"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('section')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">Birthday</label>
                <input id="date_of_birth" type="date" wire:model.defer="date_of_birth"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('date_of_birth')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[80px]">
                <label class="text-sm font-medium text-gray-700">Sex</label>
                <select id="sex" wire:model.defer="sex"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">--</option>
                    <option value="M">M</option>
                    <option value="F">F</option>
                </select>
                @error('sex')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Weight (kg)</label>
                <input id="weight" type="number" step="0.01" wire:model.defer="weight"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('weight')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Height (cm)</label>
                <input id="height" type="number" step="0.1" wire:model.defer="height"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('height')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex gap-4 items-center justify-center overflow-x-auto py-2">
            <div class="flex gap-2 items-center min-w-[200px]">
                <div class="flex flex-col w-1/2">
                    <label class="text-sm font-medium text-gray-700">Age (years)</label>
                    <input id="age_years" type="number" min="0" wire:model.defer="age_years" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    @error('age_years') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col w-1/2">
                    <label class="text-sm font-medium text-gray-700">Months</label>
                    <input id="age_months" type="number" min="0" max="11" wire:model.defer="age_months" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    @error('age_months') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">BMI</label>
                <input id="bmi" type="number" step="0.01" wire:model.defer="bmi"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('bmi')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">Nutritional Status</label>
                <input id="nutritional_status" type="text" wire:model.defer="nutritional_status"
                    placeholder="e.g. Normal / Underweight"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('nutritional_status')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[160px]">
                <label class="text-sm font-medium text-gray-700">Height for Age</label>
                <input id="height_for_age" type="text" wire:model.defer="height_for_age"
                    placeholder="e.g. Normal / Stunted"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('height_for_age')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col gap-2 min-w-[240px]">
                <label class="text-sm font-medium text-gray-700">Other flags</label>
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model.defer="fourps" class="form-checkbox" />
                        <span class="ml-2">4ps</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model.defer="ip" class="form-checkbox" />
                        <span class="ml-2">ip</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model.defer="pardo" class="form-checkbox" />
                        <span class="ml-2">pardo</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model.defer="dewormed" class="form-checkbox" />
                        <span class="ml-2">dewormed</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model.defer="parent_consent_milk" class="form-checkbox" />
                        <span class="ml-2">parent_consent_milk</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model.defer="sbfp_previous_beneficiary" class="form-checkbox" />
                        <span class="ml-2">sbfp_previous_beneficiary</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="flex items-center min-w-[160px]">
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Save</button>
        </div>
    </form>

    <script>
        (function() {
            function parseDate(value) {
                if (!value) return null;
                var d = new Date(value);
                return isNaN(d.getTime()) ? null : d;
            }


            function calcAge(dob, refDate) {
                if (!dob) return null;
                var ref = refDate || new Date();
                var years = ref.getFullYear() - dob.getFullYear();
                var months = ref.getMonth() - dob.getMonth();
                var days = ref.getDate() - dob.getDate();
                if (days < 0) {
                    months -= 1;
                }
                if (months < 0) {
                    years -= 1;
                    months += 12;
                }
                return {
                    years: years,
                    months: months
                };
            }

            function calcBMI(weight, height) {
                if (!weight || !height) return null;
                var h = height / 100; // cm -> m
                if (h <= 0) return null;
                var bmi = weight / (h * h);
                return Math.round(bmi * 100) / 100;
            }

            function calcNutritionalStatus(bmi) {
                if (bmi === null) return '';
                // Simple heuristic thresholds (approximate; adjust if needed)
                if (bmi < 16) return 'Severely wasted';
                if (bmi < 17) return 'Wasted';
                if (bmi < 25) return 'Normal';
                return 'Overweight';
            }

            // Height-for-age is computed via server API at /api/get-hfa

            function dispatchInput(el, value) {
                if (!el) return;
                el.value = value || '';
                el.dispatchEvent(new Event('input', {
                    bubbles: true
                }));
            }

            var dobEl = document.getElementById('date_of_birth');
            var weighEl = document.getElementById('date_of_weighing');
            var weightEl = document.getElementById('weight');
            var heightEl = document.getElementById('height');
            var sexEl = document.getElementById('sex');
            var ageYearsEl = document.getElementById('age_years');
            var ageMonthsEl = document.getElementById('age_months');
            var bmiEl = document.getElementById('bmi');
            var nutEl = document.getElementById('nutritional_status');
            var hfaEl = document.getElementById('height_for_age');

            function recalc() {
                var dob = parseDate(dobEl ? dobEl.value : null);
                var ref = parseDate(weighEl ? weighEl.value : null) || new Date();
                var wt = parseFloat(weightEl ? weightEl.value : NaN);
                var ht = parseFloat(heightEl ? heightEl.value : NaN);

                if (dob) {
                    var a = calcAge(dob, ref);
                    dispatchInput(ageYearsEl, a.years);
                    dispatchInput(ageMonthsEl, a.months);
                }

                var bmi = calcBMI(isNaN(wt) ? null : wt, isNaN(ht) ? null : ht);
                if (bmi !== null) {
                    dispatchInput(bmiEl, bmi);
                    dispatchInput(nutEl, calcNutritionalStatus(bmi));
                } else {
                    dispatchInput(bmiEl, '');
                    dispatchInput(nutEl, '');
                }

                var ageYears = null;
                if (dob) {
                    var a2 = calcAge(dob, ref);
                    ageYears = a2.years + (a2.months / 12);
                }
                var sexVal = sexEl && sexEl.value ? sexEl.value.toUpperCase() : null;
                if (ht && ageYears && sexVal) {
                    var ageMonthsRounded = Math.round(ageYears * 12);
                    // Only query API for ages supported by server (controller validates 60-228 months)
                    if (ageMonthsRounded >= 60 && ageMonthsRounded <= 228) {
                        var genderParam = (sexVal === 'M') ? 'male' : (sexVal === 'F' ? 'female' : '');
                        if (genderParam) {
                            fetch('/api/get-hfa?age_months=' + encodeURIComponent(ageMonthsRounded) + '&height_cm=' + encodeURIComponent(ht) + '&gender=' + encodeURIComponent(genderParam))
                                .then(function(resp){ if (resp.ok) return resp.json(); throw new Error('HFA API failed'); })
                                .then(function(json){
                                    dispatchInput(hfaEl, json.status || '');
                                })
                                .catch(function(err){
                                    console.error('HFA API error', err);
                                    dispatchInput(hfaEl, '');
                                });
                        } else {
                            dispatchInput(hfaEl, '');
                        }
                    } else {
                        // Age out of API supported range — leave blank
                        dispatchInput(hfaEl, '');
                    }
                } else {
                    dispatchInput(hfaEl, '');
                }
            }

            [dobEl, weighEl, weightEl, heightEl, sexEl].forEach(function(el) {
                if (!el) return;
                el.addEventListener('input', recalc);
                el.addEventListener('change', recalc);
            });


            // initial calc on load
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(recalc, 250);
            });

            // update lastlyAddedPupil when a pupil is saved via Livewire
            window.addEventListener('pupil-saved', function(e) {
                try {
                    var el = document.getElementById('lastlyAddedPupil');
                    if (!el) return;
                    el.textContent = e && e.detail && e.detail.name ? e.detail.name : 'Student Lastly added';
                } catch (err) {
                    console.error('Error updating lastlyAddedPupil', err);
                }
            });
        })();
    </script>
</div>
