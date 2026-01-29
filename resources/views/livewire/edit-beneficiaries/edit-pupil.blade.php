@php $instance = $pupil->id ?? uniqid(); @endphp
<div class="mx-auto bg-white rounded-lg shadow p-6" data-instance="{{ $instance }}">
    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4 gap-4">
        <h3 class="text-lg font-semibold">Edit Pupil</h3>

        <div class="flex flex-col items-center items-end gap-2">
            <label class="text-sm font-medium text-gray-700">Date of weighing</label>
            <input type="date" id="date_of_weighing" wire:model="date_of_weighing"
                class="mt-1 block rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            @error('date_of_weighing')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <form wire:submit.prevent="savePupil">
        <div class="flex gap-4 items-start justify-center overflow-x-auto py-2">
            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">First Name</label>
                <input id="first_name" type="text" wire:model="first_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('first_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">Last Name</label>
                <input id="last_name" type="text" wire:model="last_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('last_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[120px]">
                <label class="text-sm font-medium text-gray-700">Suffix</label>
                <input id="suffix_name" type="text" wire:model="suffix_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('suffix_name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Grade</label>
                <select id="grade" wire:model="grade" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">--</option>
                    <option value="k">Kinder</option>
                    <option value="1">Grade 1</option>
                    <option value="2">Grade 2</option>
                    <option value="3">Grade 3</option>
                    <option value="4">Grade 4</option>
                    <option value="5">Grade 5</option>
                    <option value="6">Grade 6</option>
                    <option value="7">Grade 7</option>
                    <option value="8">Grade 8</option>
                    <option value="9">Grade 9</option>
                    <option value="10">Grade 10</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                </select>
                @error('grade')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Section</label>
                <input id="section" type="text" wire:model="section" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('section')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex gap-4 items-start justify-center overflow-x-auto py-2">
            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">Birthday</label>
                <input id="date_of_birth" type="date" wire:model="date_of_birth"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('date_of_birth')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[80px]">
                <label class="text-sm font-medium text-gray-700">Sex</label>
                <select id="sex" wire:model="sex"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">--</option>
                    <option value="m">M</option>
                    <option value="f">F</option>
                </select>
                @error('sex')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Weight (kg)</label>
                <input id="weight" type="number" step="0.01" wire:model="weight"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('weight')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">Height (cm)</label>
                <input id="height" type="number" step="0.1" wire:model="height" wire:change="getHFA"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('height')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col gap-2 min-w-[240px]">
                <label class="text-sm font-medium text-gray-700">Other flags</label>
                <div class="flex flex-wrap gap-2">
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model="fourps" class="form-checkbox" />
                        <span class="ml-2">4ps</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model="ip" class="form-checkbox" />
                        <span class="ml-2">ip</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model="pardo" class="form-checkbox" />
                        <span class="ml-2">pardo</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model="dewormed" class="form-checkbox" />
                        <span class="ml-2">dewormed</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model="parent_consent_milk" class="form-checkbox" />
                        <span class="ml-2">parent_consent_milk</span>
                    </label>
                    <label class="inline-flex items-center text-sm">
                        <input type="checkbox" wire:model="sbfp_previous_beneficiary" class="form-checkbox" />
                        <span class="ml-2">sbfp_previous_beneficiary</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="flex gap-4 items-center justify-center overflow-x-auto py-2">
            <div class="flex gap-2 items-center min-w-[200px]">
                <div class="flex flex-col w-1/2">
                    <label class="text-sm font-medium text-gray-700">Age (years)</label>
                    <input id="age_years" type="number" min="0" wire:model="age_years"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    @error('age_years') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col w-1/2">
                    <label class="text-sm font-medium text-gray-700">Months</label>
                    <input id="age_months" type="number" min="0" max="11" wire:model="age_months"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                    @error('age_months') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex flex-col min-w-[140px]">
                <label class="text-sm font-medium text-gray-700">BMI</label>
                <input id="bmi" type="number" step="0.01" wire:model="bmi"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('bmi')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">Nutritional Status</label>
                <input id="nutritional_status" type="text" wire:model="nutritional_status"
                    placeholder="e.g. Normal / Underweight"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('nutritional_status')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[160px]">
                <label class="text-sm font-medium text-gray-700">Height for Age</label>
                <input id="height_for_age" type="text" wire:model="height_for_age" placeholder="e.g. Normal / Stunted"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('height_for_age')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <div class="flex items-center min-w-[160px]">
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Save</button>
        </div>
    </form>

    <script>
        (function () {
            var root = document.querySelector('[data-instance="{{ $instance }}"]');
            if (!root) return;

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

            var dobEl = root.querySelector('#date_of_birth');
            var weighEl = root.querySelector('#date_of_weighing');
            var weightEl = root.querySelector('#weight');
            var heightEl = root.querySelector('#height');
            var sexEl = root.querySelector('#sex');
            var ageYearsEl = root.querySelector('#age_years');
            var ageMonthsEl = root.querySelector('#age_months');
            var bmiEl = root.querySelector('#bmi');
            var nutEl = root.querySelector('#nutritional_status');
            var hfaEl = root.querySelector('#height_for_age');

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
                    // HFA is now computed server-side by Livewire `getHFA` when the height field changes.
                    // Do not compute or assign `height_for_age` here to avoid race conditions.
                } else {
                    dispatchInput(hfaEl, '');
                }
            }

            [dobEl, weighEl, weightEl, heightEl, sexEl].forEach(function (el) {
                if (!el) return;
                el.addEventListener('input', recalc);
                el.addEventListener('change', recalc);
            });

            // initial calc on load (run immediately for this component instance)
            setTimeout(recalc, 250);

            // update lastlyAddedPupil when a pupil is saved via Livewire
            window.addEventListener('pupil-saved', function (e) {
                try {
                    var el = root.querySelector('#lastlyAddedPupil');
                    if (!el) return;
                    el.textContent = e && e.detail && e.detail.name ? e.detail.name : 'Student Lastly added';
                } catch (err) {
                    console.error('Error updating lastlyAddedPupil', err);
                }
            });
        })();
    </script>
</div>