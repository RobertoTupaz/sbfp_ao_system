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

            <div class="flex flex-col min-w-[180px]">
                <label class="text-sm font-medium text-gray-700">Birthday</label>
                <input id="date_of_birth" type="date" wire:model.defer="date_of_birth"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                @error('date_of_birth')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col min-w-[140px]">
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

            var whoHfaTable = null;
            var whoHfaLoaded = false;
            // load WHO sample table (replace with full WHO table for production)
            fetch('/data/who_hfa_sample.json')
                .then(function(resp){ if (resp.ok) return resp.json(); throw new Error('WHO HFA fetch failed'); })
                .then(function(json){ whoHfaTable = json; whoHfaLoaded = true; try { recalc(); } catch(e){} })
                .catch(function(err){ console.error('WHO HFA load failed', err); whoHfaTable = null; whoHfaLoaded = false; });

            function calcHeightForAge(height, ageYears, sex) {
                if (!height || !ageYears) return '';
                var ageMonths = Math.round(ageYears * 12);
                sex = (sex || '').toUpperCase();
                if (!whoHfaTable || !sex || !whoHfaTable[sex]) {
                    // WHO table unavailable or sex missing — do not provide a misleading classification
                    return '';
                }
                var table = whoHfaTable[sex] || whoHfaTable['M'];
                // find nearest available age key
                var keys = Object.keys(table).map(Number).sort(function(a,b){return a-b;});
                var nearest = keys.reduce(function(prev, curr){
                    return (Math.abs(curr - ageMonths) < Math.abs(prev - ageMonths) ? curr : prev);
                }, keys[0]);
                var thresholds = table[String(nearest)];
                if(!thresholds) return '';
                if(height <= thresholds.minus3) return 'Severely stunted';
                if(height <= thresholds.minus2) return 'Stunted';
                if(height >= thresholds.plus2) return 'Tall';
                return 'Normal';
            }

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
                if (ht && ageYears) {
                    if (whoHfaLoaded && whoHfaTable && sexVal) {
                        dispatchInput(hfaEl, calcHeightForAge(ht, Math.max(1, ageYears), sexVal));
                    } else {
                        // WHO data not ready or sex missing — leave blank to avoid misleading result
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
        })();
    </script>
</div>
