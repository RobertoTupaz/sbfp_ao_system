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
    public $setBeneficiaries = false;
    public $search = '';
    public function mount() {
        $this->getBeneficiearies();
    }

    public function searchBeneficiaries()
    {
        $query = NutritionalStatus::query()->where('isBeneficiary', true);

        if ($this->search && trim($this->search) !== '') {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', $s)
                    ->orWhere('grade', 'like', $s)
                    ->orWhere('section', 'like', $s);
            });
        }

        $this->beneficiaries = $query->get();
        $this->setBeneficiaries = true;
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->getBeneficiearies();
    }

    public function getBeneficiearies() {
        $this->beneficiaries = NutritionalStatus::where('isBeneficiary', "=", true)->get();
        $this->setBeneficiaries = true;
    }

    public function render()
    {
        return view('livewire.edit-beneficiaries.beneficiaries-list');
    }

    #[On('primary_secondary_saved')]
    public function getBeneficiaries()
    {
        NutritionalStatus::query()->update(['isBeneficiary' => false]);

        $beneficiariesCount = Beneficiaries::first();
        $primary = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();

        //setting beneficiaries as a collection with no value
        $beneficiaries = NutritionalStatus::where('grade', '100')->get();
        $remaining = $beneficiariesCount->beneficiaries_count;
        Log::info($beneficiaries->count());

        if ($primary->all_kinder == true) {
            $allKinderBeneficiaries = NutritionalStatus::where('grade', '=', 'k')
                ->limit($remaining)
                ->get();

            Log::info($allKinderBeneficiaries->count());
            $beneficiaries = $beneficiaries->merge($allKinderBeneficiaries);
            $remaining = $beneficiariesCount->beneficiaries_count - $allKinderBeneficiaries->count();
        }

        if ($primary->all_grade_1 == true) {
            $allGrade1 = NutritionalStatus::where('grade', '=', '1')
                ->limit($remaining)
                ->get();

            Log::info($allGrade1->count());
            $beneficiaries = $beneficiaries->merge($allGrade1);
            $remaining = $beneficiariesCount->beneficiaries_count - $allGrade1->count();
        }

        if ($primary->all_grade_2 == true) {
            $allGrade2 = NutritionalStatus::where('grade', '=', '2')
                ->limit($remaining)
                ->get();

            Log::info($allGrade2->count());
            $beneficiaries = $beneficiaries->merge($allGrade2);
            $remaining = $beneficiariesCount->beneficiaries_count - $allGrade2->count();
        }

        if ($primary->all_grade_3 == true) {
            $allGrade3 = NutritionalStatus::where('grade', '=', '3')
                ->limit($remaining)
                ->get();

            Log::info($allGrade3->count());
            $beneficiaries = $beneficiaries->merge($allGrade3);
            $remaining = $beneficiariesCount->beneficiaries_count - $allGrade3->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {
            //get primary beneficiaries
            $primarybeneficiaries = NutritionalStatus::whereIn('nutritional_status', [
                'Severely Wasted',
                'Wasted',
            ])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($primarybeneficiaries);
            $remaining = $beneficiariesCount->beneficiaries_count - $primarybeneficiaries->count();
        }

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


        Log::info($beneficiaries->count());

        $setBeneficiariesLocal = $beneficiaries->each(function ($beneficiary) {
            $beneficiary->isBeneficiary = true;
            $beneficiary->save();
        });

        if ($setBeneficiariesLocal) {
            $this->beneficiaries = $beneficiaries;
            $this->setBeneficiaries = true;
        } else {
            $this->setBeneficiaries = false;
        }
    }
}
