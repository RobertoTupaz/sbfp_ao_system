<?php

namespace App\Livewire\EditBeneficiaries;

use Livewire\Component;
use App\Models\NutritionalStatus;

class GoToGenerateForms extends Component
{
    public $hasBeneficiaries = false;

    protected $listeners = [
        'beneficiaries-saved' => 'refreshVisibility',
    ];

    public function mount()
    {
        $this->refreshVisibility();
    }

    public function refreshVisibility()
    {
        $this->hasBeneficiaries = NutritionalStatus::where('isBeneficiary', true)->exists();
    }
    public function render()
    {
        return view('livewire.edit-beneficiaries.go-to-generate-forms');
    }
}
