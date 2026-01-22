<?php

namespace App\Livewire\EditBeneficiaries;

use App\Models\Beneficiaries;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BeneficiariesCount extends Component
{
    public $beneficiariesCount;

    public function mount()
    {
        $this->beneficiariesCount = Beneficiaries::first()->beneficiaries_count;
    }
    public function render()
    {
        return view('livewire.edit-beneficiaries.beneficiaries-count');
    }

    public function saveBeneficiariesCount()
    {
        try {
            $this->validate([
                'beneficiariesCount' => 'required|integer|min:0',
            ]);

            $beneficiaries = Beneficiaries::first();
            $beneficiaries->beneficiaries_count = $this->beneficiariesCount;
            $beneficiaries->save();

            session()->flash('success', 'Beneficiaries count saved.');
            Log::info('Beneficiaries count saved: ' . $this->beneficiariesCount);
            $this->dispatch('beneficiaries-saved', ['count' => $this->beneficiariesCount]);
        } catch (\Throwable $e) {
            Log::error('Error saving beneficiaries count: ' . $e->getMessage());
            session()->flash('error', 'Failed to save beneficiaries count: ' . $e->getMessage());
        }
    }
}
