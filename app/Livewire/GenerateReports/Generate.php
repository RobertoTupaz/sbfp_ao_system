<?php

namespace App\Livewire\GenerateReports;

use Livewire\Component;
use App\Models\SchoolYear;
use App\Models\State;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

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

        // prefer any previously saved selections in session (set by other components)
        $defaultValue = in_array((string) $defaultYear, $this->schoolYears, true)
            ? (string) $defaultYear
            : '';

        $this->selectedYear = session('focal_selected_year', $defaultValue);
        $this->selectedStateGlobal = session('focal_selected_state', '');
    }

    public function updatedSelectedYear()
    {
        // Placeholder for handling selection changes if needed
    }
    public function updatedSelectedStateGlobal()
    {
        // placeholder if state-specific actions are needed
    }

    public function saveSelections()
    {
        session([
            'focal_selected_state' => $this->selectedStateGlobal,
            'focal_selected_year' => $this->selectedYear,
        ]);

        // $this->dispatch('focal-selection-saved', ['state' => $this->selectedStateGlobal, 'year' => $this->selectedYear]);
        $this->notif();
    }

    public function notif() {
        LivewireAlert::title('Changes saved!')
        ->success()
        ->show();
    }

    public function render()
    {
        return view('livewire.generate-reports.generate');
    }
}
