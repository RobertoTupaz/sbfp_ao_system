<?php

namespace App\Livewire\TrackEnrollees;

use App\Models\HfaSimplifiedVersion;
use App\Models\NutritionalStatus;
use App\Models\SwappedPupils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Base extends Component
{
    public $gradeCounts = [];

    public $selectedGrade = null;

    public $sectionCounts = [];

    public $selectedSection = null;

    public $students = [];

    public $pupilSearch = '';

    public $selectedSectionPupilCount = 0;

    public $nutritionalStatusPercentages = [
        'severely wasted' => 0,
        'wasted' => 0,
        'normal' => 0,
        'overweight' => 0,
        'obese' => 0,
    ];

    public $heightForAgePercentages = [
        'severely stunted' => 0,
        'stunted' => 0,
        'normal' => 0,
        'tall' => 0,
    ];

    public $showDeleteAll = false;

    // editing state
    public $editingStudent = null;

    public $editingHeight = null;

    public $editingWeight = null;

    public function render()
    {
        $this->loadGradeCounts();

        return view('livewire.track-enrollees.base');
    }

    protected function loadGradeCounts()
    {
        $grades = array_merge(['k'], array_map('strval', range(1, 12)), ['non_graded']);

        $counts = NutritionalStatus::query()
            ->select('grade', DB::raw('count(*) as total'))
            ->whereIn('grade', $grades)
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->toArray();

        $noHwCounts = NutritionalStatus::query()
            ->select('grade', DB::raw('count(*) as total'))
            ->whereIn('grade', $grades)
            ->whereNull('height')
            ->whereNull('weight')
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->toArray();

        $this->gradeCounts = [];
        foreach ($grades as $g) {
            $this->gradeCounts[$g] = [
                'total' => isset($counts[$g]) ? (int) $counts[$g] : 0,
                'no_hw' => isset($noHwCounts[$g]) ? (int) $noHwCounts[$g] : 0,
            ];
        }
    }

    public function loadSectionCounts($grade)
    {
        $this->selectedGrade = $grade;
        $sections = NutritionalStatus::query()
            ->select('section', DB::raw('count(*) as total'))
            ->where('grade', $grade)
            ->groupBy('section')
            ->orderBy('section')
            ->get();

        $noHwCounts = NutritionalStatus::query()
            ->select('section', DB::raw('count(*) as total'))
            ->where('grade', $grade)
            ->whereNull('height')
            ->whereNull('weight')
            ->groupBy('section')
            ->pluck('total', 'section')
            ->toArray();

        $this->sectionCounts = $sections->map(function ($row) use ($noHwCounts) {
            $sectionValue = $row->section;
            $label = $sectionValue ?: 'Unspecified';

            return [
                'section' => $sectionValue,
                'label' => $label,
                'count' => (int) $row->total,
                'no_hw' => isset($noHwCounts[$sectionValue]) ? (int) $noHwCounts[$sectionValue] : 0,
            ];
        })->toArray();
        // clear any previously loaded students
        $this->selectedSection = null;
        $this->students = [];
        $this->pupilSearch = '';
        $this->selectedSectionPupilCount = 0;
        $this->resetSectionPercentages();
    }

    public function clearSectionCounts()
    {
        $this->selectedGrade = null;
        $this->sectionCounts = [];
        $this->clearStudents();
    }

    public function loadStudents($section)
    {
        // $section may be empty string or null for unspecified
        $this->selectedSection = $section;
        $this->pupilSearch = '';
        $this->refreshStudents();
    }

    protected function refreshStudents()
    {
        $query = NutritionalStatus::query()->where('grade', $this->selectedGrade);
        if ($this->selectedSection === '' || is_null($this->selectedSection)) {
            $query->where(function ($q) {
                $q->whereNull('section')->orWhere('section', '');
            });
        } else {
            $query->where('section', $this->selectedSection);
        }

        $this->selectedSectionPupilCount = (clone $query)->count();
        $this->loadSectionPercentages($query);

        $search = trim($this->pupilSearch);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $like = '%'.$search.'%';

                $q->where('full_name', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('suffix_name', 'like', $like);
            });
        }

        $this->students = $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->toArray();
    }

    protected function loadSectionPercentages($query)
    {
        $nutritionalCounts = (clone $query)
            ->selectRaw('LOWER(TRIM(nutritional_status)) as status, COUNT(*) as total')
            ->whereNotNull('nutritional_status')
            ->groupByRaw('LOWER(TRIM(nutritional_status))')
            ->pluck('total', 'status');

        $heightForAgeCounts = (clone $query)
            ->selectRaw('LOWER(TRIM(height_for_age)) as status, COUNT(*) as total')
            ->whereNotNull('height_for_age')
            ->groupByRaw('LOWER(TRIM(height_for_age))')
            ->pluck('total', 'status');

        foreach (array_keys($this->nutritionalStatusPercentages) as $status) {
            $this->nutritionalStatusPercentages[$status] = $this->percentage(
                (int) $nutritionalCounts->get($status, 0)
            );
        }

        foreach (array_keys($this->heightForAgePercentages) as $status) {
            $this->heightForAgePercentages[$status] = $this->percentage(
                (int) $heightForAgeCounts->get($status, 0)
            );
        }
    }

    protected function percentage(int $count): float
    {
        if ($this->selectedSectionPupilCount === 0) {
            return 0;
        }

        return round(($count / $this->selectedSectionPupilCount) * 100, 1);
    }

    protected function resetSectionPercentages()
    {
        $this->nutritionalStatusPercentages = array_fill_keys(
            array_keys($this->nutritionalStatusPercentages),
            0
        );
        $this->heightForAgePercentages = array_fill_keys(
            array_keys($this->heightForAgePercentages),
            0
        );
    }

    public function updatedPupilSearch()
    {
        if ($this->selectedGrade !== null && $this->selectedSection !== null) {
            $this->refreshStudents();
        }
    }

    public function clearPupilSearch()
    {
        $this->pupilSearch = '';
        $this->refreshStudents();
    }

    public function clearStudents()
    {
        $this->selectedSection = null;
        $this->students = [];
        $this->pupilSearch = '';
        $this->selectedSectionPupilCount = 0;
        $this->resetSectionPercentages();
    }

    public function deleteAllPupils()
    {
        DB::transaction(function () {
            SwappedPupils::query()->delete();
            NutritionalStatus::query()->update(['deleted_by' => auth()->id()]);
            NutritionalStatus::query()->delete();
        });
        session()->flash('success', 'All pupils moved to Retain Deleted');

        $this->selectedGrade = null;
        $this->sectionCounts = [];
        $this->selectedSection = null;
        $this->students = [];
        $this->pupilSearch = '';
        $this->selectedSectionPupilCount = 0;
        $this->resetSectionPercentages();
        $this->showDeleteAll = false;
        $this->loadGradeCounts();
    }

    public function deleteSection()
    {
        if (! $this->selectedGrade || $this->selectedSection === null) {
            session()->flash('error', 'No section selected');

            return;
        }

        $query = NutritionalStatus::query()->where('grade', $this->selectedGrade);
        if ($this->selectedSection === '' || is_null($this->selectedSection)) {
            $query->where(function ($q) {
                $q->whereNull('section')->orWhere('section', '');
            });
        } else {
            $query->where('section', $this->selectedSection);
        }

        DB::transaction(function () use ($query) {
            $ids = (clone $query)->pluck('id');
            SwappedPupils::whereIn('old_pupil_id', $ids)->orWhereIn('new_pupil_id', $ids)->delete();
            NutritionalStatus::whereIn('id', $ids)->update(['deleted_by' => auth()->id()]);
            NutritionalStatus::whereIn('id', $ids)->delete();
        });

        $sectionLabel = $this->selectedSection ?: 'Unspecified';
        session()->flash('success', "Section \"{$sectionLabel}\" moved to Retain Deleted");

        $this->selectedSection = null;
        $this->students = [];
        $this->pupilSearch = '';
        $this->selectedSectionPupilCount = 0;
        $this->loadSectionCounts($this->selectedGrade);
        $this->loadGradeCounts();
    }

    public function deletePupil($studentId)
    {
        $pupil = NutritionalStatus::find($studentId);
        if (! $pupil) {
            session()->flash('error', 'Pupil not found');

            return;
        }

        DB::transaction(function () use ($pupil) {
            SwappedPupils::where('old_pupil_id', $pupil->id)
                ->orWhere('new_pupil_id', $pupil->id)
                ->delete();
            $pupil->delete();
        });
        session()->flash('success', 'Pupil moved to Retain Deleted');

        $this->loadGradeCounts();
        if ($this->selectedGrade !== null && $this->selectedSection !== null) {
            $this->refreshStudents();
        }
    }

    public function startEdit($studentId)
    {
        $pupil = NutritionalStatus::find($studentId);
        if (! $pupil) {
            session()->flash('error', 'Pupil not found');

            return;
        }

        $this->editingStudent = $pupil->id;
        // convert stored meters to centimeters for editing
        $this->editingHeight = $pupil->height !== null ? $pupil->height : null;
        $this->editingWeight = $pupil->weight;
    }

    public function cancelEdit()
    {
        $this->editingStudent = null;
        $this->editingHeight = null;
        $this->editingWeight = null;
    }

    public function saveEdit()
    {
        $this->validate([
            'editingHeight' => 'nullable|numeric|min:0',
            'editingWeight' => 'nullable|numeric|min:0',
        ]);

        if (! $this->editingStudent) {
            session()->flash('error', 'No pupil selected for edit');

            return;
        }

        $pupil = NutritionalStatus::find($this->editingStudent);
        if (! $pupil) {
            session()->flash('error', 'Pupil not found');
            $this->cancelEdit();

            return;
        }

        // Recalculate age to today before computing BMI / HFA
        if ($pupil->birthday) {
            $dob = Carbon::parse($pupil->birthday);
            $now = Carbon::now();
            $years = $dob->diffInYears($now);
            $months = $dob->diffInMonths($now) - ($years * 12);
            $pupil->age_years = $years;
            $pupil->age_months = $months;
        }
        $pupil->date_of_weighing = Carbon::today()->toDateString();

        $heightCm = $this->editingHeight !== null ? (float) $this->editingHeight : null;
        $heightM = $heightCm !== null ? $heightCm / 100 : null;

        $pupil->weight = $this->editingWeight;
        $pupil->height = $heightCm;
        $pupil->bmi = ($heightM && $pupil->weight) ? ($pupil->weight / ($heightM * $heightM)) : null;
        $pupil->nutritional_status = $this->getBMIStatus($pupil->bmi, $pupil);
        $pupil->height_for_age = $this->getHFAStatus($pupil);
        $pupil->save();

        session()->flash('success', 'Pupil updated');

        // refresh counts and students for current selection
        $this->loadGradeCounts();
        if ($this->selectedGrade !== null && $this->selectedSection !== null) {
            $this->refreshStudents();
        }

        $this->cancelEdit();
    }

    public function getHFA($ageInMonths, $height, $gender)
    {
        // This method is not used by the component logic anymore.
        // Keep it for compatibility if other code calls it.
        $ageInMonths = (int) $ageInMonths;
        $height = (float) $height;
        $gender = strtolower($gender) === 'm' || strtolower($gender) === 'male' ? 'male' : 'female';

        $hfa = HfaSimplifiedVersion::where('month', $ageInMonths)
            ->where('gender', $gender)
            ->first();

        if (! $hfa) {
            return null;
        }

        if ($height < $hfa->less_negative_3sd) {
            return 'Severely Stunted';
        }

        if ($height <= $hfa->to_less_negative_2sd) {
            return 'Stunted';
        }

        if ($height <= $hfa->to_positive_2sd) {
            return 'Normal';
        }

        return 'Tall';
    }

    /**
     * Determine BMI category from BMI value.
     */
    /**
     * Determine BMI category by calling the internal API route `/api/get-bmi`.
     * Accepts an optional $pupil object to derive age and gender.
     */
    public function getBMIStatus($bmi, $pupil = null)
    {
        if ($bmi === null) {
            return null;
        }

        if ($pupil === null) {
            if (! $this->editingStudent) {
                return null;
            }
            $pupil = NutritionalStatus::find($this->editingStudent);
            if (! $pupil) {
                return null;
            }
        }

        $ageInMonths = 0;
        if (isset($pupil->age_years) || isset($pupil->age_months)) {
            $ageInMonths = ((int) ($pupil->age_years ?? 0) * 12) + (int) ($pupil->age_months ?? 0);
        }

        $gender = strtolower($pupil->sex ?? 'm') === 'm' ? 'male' : 'female';

        if ($ageInMonths <= 0) {
            return null;
        }

        try {
            $response = Http::timeout(5)->post(url('/api/get-bmi'), [
                'age_months' => $ageInMonths,
                'bmi' => $bmi,
                'gender' => $gender,
            ]);

            if ($response->successful() && $response->json('status')) {
                return strtolower($response->json('status'));
            }
        } catch (\Exception $e) {
            Log::warning('getBMIStatus API call failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Compute height-for-age status for a pupil. If $pupil is null, use the currently editing pupil.
     */
    public function getHFAStatus($pupil = null)
    {
        if ($pupil === null) {
            if (! $this->editingStudent) {
                return null;
            }
            $pupil = NutritionalStatus::find($this->editingStudent);
            if (! $pupil) {
                return null;
            }
        }

        $ageInMonths = 0;
        if (isset($pupil->age_years) || isset($pupil->age_months)) {
            $ageInMonths = ((int) ($pupil->age_years ?? 0) * 12) + (int) ($pupil->age_months ?? 0);
        }

        $height = $pupil->height;
        $gender = strtolower($pupil->sex ?? 'm') === 'm' ? 'male' : 'female';

        if ($ageInMonths <= 0 || $height === null) {
            return null;
        }

        $hfa = HfaSimplifiedVersion::where('month', $ageInMonths)
            ->where('gender', $gender)
            ->first();

        if (! $hfa) {
            return null;
        }

        if ($height < $hfa->less_negative_3sd) {
            return 'Severely Stunted';
        }

        if ($height <= $hfa->to_less_negative_2sd) {
            return 'Stunted';
        }

        if ($height <= $hfa->to_positive_2sd) {
            return 'Normal';
        }

        return 'Tall';
    }
}
