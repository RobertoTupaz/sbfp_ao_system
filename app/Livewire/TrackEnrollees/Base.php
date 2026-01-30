<?php

namespace App\Livewire\TrackEnrollees;

use Livewire\Component;
use App\Models\NutritionalStatus;
use Illuminate\Support\Facades\DB;

class Base extends Component
{
    public $gradeCounts = [];
    public $selectedGrade = null;
    public $sectionCounts = [];
    public $selectedSection = null;
    public $students = [];

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
}
