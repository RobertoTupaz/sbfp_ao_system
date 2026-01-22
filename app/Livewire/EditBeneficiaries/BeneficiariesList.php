<?php

namespace App\Livewire\EditBeneficiaries;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\PrimarySecondaryBeneficiaries;
use App\Models\Beneficiaries;
use App\Models\NutritionalStatus;
use Illuminate\Support\Facades\Log;

class BeneficiariesList extends Component
{
    public $beneficiaries;
    public function mount() {}

    public function render()
    {
        return view('livewire.edit-beneficiaries.beneficiaries-list');
    }

    #[On('primary_secondary_saved')]
    public function getBeneficiaries()
    {
        $beneficiariesCount = Beneficiaries::first();
        $primary = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();
        $beneficiaries = null;

        $beneficiaries = NutritionalStatus::query();

        if ($primary->all_kinder == false) {
            $beneficiaries = $beneficiaries->where('grade', 'like', 'K%');
        } else {
            //get primary beneficiaries
            $beneficiaries = $beneficiaries->whereIn('nutritional_status', [
                'Severely Wasted',
                'Wasted',
            ])->get();
            Log::info($beneficiaries->count());

            if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

                $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

                $phase1 = NutritionalStatus::where('nutritional_status', 'Normal')
                    ->whereIn('height_for_age', ['Severely Stunted', 'Stunted'])
                    ->whereNotIn('id', $beneficiaries->pluck('id'))
                    ->limit($remaining)
                    ->get();

                $beneficiaries = $beneficiaries->merge($phase1);
                Log::info($phase1->count());
            }

            if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

                $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

                $phase2 = NutritionalStatus::where('nutritional_status', 'Normal')
                    ->whereIn('height_for_age', ['Normal', 'Tall'])
                    ->where(function ($q) {
                        $q->where('_4ps', 1)
                            ->orWhere('ip', 1)
                            ->orWhere('pardo', 1);
                    })
                    ->whereNotIn('id', $beneficiaries->pluck('id'))
                    ->limit($remaining)
                    ->get();

                $beneficiaries = $beneficiaries->merge($phase2);

                Log::info($phase2->count());
            }
        }

        Log::info($beneficiaries->count());

        $beneficiaries->each(function ($beneficiary) {
            $beneficiary->isBeneficiary = true;
            $beneficiary->save();
        });
    }
}
