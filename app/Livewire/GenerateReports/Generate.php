<?php

namespace App\Livewire\GenerateReports;

use Livewire\Component;
use App\Models\SchoolYear;
use App\Models\State;

class Generate extends Component
{
    public $schoolYears = [];
    public $selectedYear = '';
    public $states = [];
    public $selectedStateGlobal = '';

    public function mount()
    {
        $this->schoolYears = SchoolYear::orderBy('school_year')->pluck('school_year')->toArray();
        $this->states = State::orderBy('id')->pluck('name')->toArray();

        $month = (int) date('n');
        $year = (int) date('Y');
        $defaultYear = ($month >= 6) ? $year : ($year - 1);

        $this->selectedYear = in_array((string) $defaultYear, $this->schoolYears, true)
            ? (string) $defaultYear
            : '';
        $this->selectedStateGlobal = '';
    }

    public function updatedSelectedYear()
    {
        // Placeholder for handling selection changes if needed
    }
    public function updatedSelectedStateGlobal()
    {
        // placeholder if state-specific actions are needed
    }
    public function render()
    {
        return view('livewire.generate-reports.generate');
    }
}
