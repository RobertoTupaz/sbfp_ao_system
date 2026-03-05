<?php

namespace App\Livewire\TrackEnrollees;

use App\Models\HfaSimplifiedVersion;
use Livewire\Component;
use App\Models\NutritionalStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Base extends Component
{
    public $gradeCounts = [];
    public $selectedGrade = null;
    public $sectionCounts = [];
    public $selectedSection = null;
    public $students = [];
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
        $grades = array_merge(['k'], array_map('strval', range(1, 12)));

        $counts = NutritionalStatus::query()
            ->select('grade', DB::raw('count(*) as total'))
            ->whereIn('grade', $grades)
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->toArray();

        // ensure all grades exist in the array
        $this->gradeCounts = [];
        foreach ($grades as $g) {
            $this->gradeCounts[$g] = isset($counts[$g]) ? (int)$counts[$g] : 0;
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

        // store entries as arrays with actual section value and display label
        $this->sectionCounts = $sections->map(function ($row) {
            $sectionValue = $row->section;
            $label = $sectionValue ?: 'Unspecified';
            return [
                'section' => $sectionValue,
                'label' => $label,
                'count' => (int)$row->total,
            ];
        })->toArray();
        // clear any previously loaded students
        $this->selectedSection = null;
        $this->students = [];
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

        $query = NutritionalStatus::query()->where('grade', $this->selectedGrade);
        if ($section === '' || is_null($section)) {
            $query->where(function ($q) {
                $q->whereNull('section')->orWhere('section', '');
            });
        } else {
            $query->where('section', $section);
        }

        $this->students = $query->orderBy('last_name')->get()->toArray();
    }

    public function clearStudents()
    {
        $this->selectedSection = null;
        $this->students = [];
    }

    public function startEdit($studentId)
    {
        $pupil = NutritionalStatus::find($studentId);
        if (!$pupil) {
            session()->flash('error', 'Pupil not found');
            return;
        }

        $this->editingStudent = $pupil->id;
        // convert stored meters to centimeters for editing
        $this->editingHeight = $pupil->height !== null ? ($pupil->height * 100) : null;
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

        if (!$this->editingStudent) {
            session()->flash('error', 'No pupil selected for edit');
            return;
        }

        $pupil = NutritionalStatus::find($this->editingStudent);
        if (!$pupil) {
            session()->flash('error', 'Pupil not found');
            $this->cancelEdit();
            return;
        }

        // convert centimeters back to meters before saving
        $pupil->height = $this->editingHeight !== null ? ($this->editingHeight / 100) : null;
        $pupil->weight = $this->editingWeight;
        $pupil->bmi = ($pupil->height && $pupil->weight) ? ($pupil->weight / ($pupil->height * $pupil->height)) : null;
        $pupil->nutritional_status = $this->getBMIStatus($pupil->bmi);
        $pupil->height_for_age = $this->getHFAStatus();
        $pupil->save();

        session()->flash('success', 'Pupil updated');

        // refresh counts and students for current selection
        $this->loadGradeCounts();
        if ($this->selectedGrade !== null && $this->selectedSection !== null) {
            $this->loadStudents($this->selectedSection);
        }

        $this->cancelEdit();
    }

    public function getHFA($ageInMonths, $height, $gender)
    {
        // This method is not used by the component logic anymore.
        // Keep it for compatibility if other code calls it.
        $ageInMonths = (int)$ageInMonths;
        $height = (float)$height;
        $gender = strtolower($gender) === 'm' || strtolower($gender) === 'male' ? 'male' : 'female';

        Log::info($ageInMonths . ' ' . $height . ' ' . $gender);
        $hfa = HfaSimplifiedVersion::where('month', $ageInMonths)
            ->where('gender', $gender)
            ->first();

        Log::info($hfa);
        if (!$hfa) {
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
    public function getBMIStatus($bmi)
    {
        if ($bmi === null) {
            return null;
        }

        // Simple heuristic thresholds suitable for seeded/test data.
        if ($bmi < 16.0) {
            return 'severely wasted';
        }

        if ($bmi < 17.0) {
            return 'wasted';
        }

        if ($bmi < 25.0) {
            return 'normal';
        }

        return 'overweight';
    }

    /**
     * Compute height-for-age status for a pupil. If $pupil is null, use the currently editing pupil.
     */
    public function getHFAStatus($pupil = null)
    {
        if ($pupil === null) {
            if (!$this->editingStudent) {
                return null;
            }
            $pupil = NutritionalStatus::find($this->editingStudent);
            if (!$pupil) {
                return null;
            }
        }

        $ageInMonths = 0;
        if (isset($pupil->age_years) || isset($pupil->age_months)) {
            $ageInMonths = ((int)($pupil->age_years ?? 0) * 12) + (int)($pupil->age_months ?? 0);
        }

        $height = $pupil->height;
        $gender = strtolower($pupil->sex ?? 'm') === 'm' ? 'male' : 'female';

        if ($ageInMonths <= 0 || $height === null) {
            return null;
        }

        $hfa = HfaSimplifiedVersion::where('month', $ageInMonths)
            ->where('gender', $gender)
            ->first();

        if (!$hfa) {
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
