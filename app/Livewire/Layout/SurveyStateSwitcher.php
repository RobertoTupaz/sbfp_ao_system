<?php

namespace App\Livewire\Layout;

use App\Support\SurveyState;
use Livewire\Component;

class SurveyStateSwitcher extends Component
{
    public string $state = SurveyState::BASELINE;

    public function mount(): void
    {
        $this->state = SurveyState::current();
    }

    public function updatedState($value): void
    {
        SurveyState::set($value);

        // Reload so every component on the page re-queries the selected state
        $this->js('window.location.reload()');
    }

    public function render()
    {
        return view('livewire.layout.survey-state-switcher', ['states' => SurveyState::STATES]);
    }
}
