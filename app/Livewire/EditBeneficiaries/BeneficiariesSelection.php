<?php

namespace App\Livewire\EditBeneficiaries;

use App\Models\NutritionalStatus;
use Livewire\Component;
use App\Models\PrimarySecondaryBeneficiaries;
use Illuminate\Support\Facades\Log;

class BeneficiariesSelection extends Component
{
    public $allBeneficiaries;
    public $primary_name;
    public $primary_all_kinder;
    public $primary_all_grade_1;
    public $primary_all_grade_2;
    public $primary_all_grade_3;
    public $primary_severely_wasted;
    public $primary_wasted;
    public $primary_normal_weight;
    public $primary_overweight_obese;
    public $primary_severely_stunted;
    public $primary_stunted;
    public $primary_normal_height;
    public $primary_tall;
    public $primary_4ps;
    public $primary_ip;
    public $primary_pardo;

    public $secondary_name;
    public $secondary_all_kinder;
    public $secondary_all_grade_1;
    public $secondary_all_grade_2;
    public $secondary_all_grade_3;
    public $secondary_severely_wasted;
    public $secondary_wasted;
    public $secondary_normal_weight;
    public $secondary_overweight_obese;
    public $secondary_severely_stunted;
    public $secondary_stunted;
    public $secondary_normal_height;
    public $secondary_tall;
    public $secondary_4ps;
    public $secondary_ip;
    public $secondary_pardo;



    public function mount()
    {
        $eligibleBeneficiaries = PrimarySecondaryBeneficiaries::all();

        foreach ($eligibleBeneficiaries as $beneficiary) {
            if ($beneficiary->name == 'Primary') {
                $this->primary_name = $beneficiary->name;
                $this->primary_all_kinder = (bool) $beneficiary->all_kinder;
                $this->primary_all_grade_1 = (bool) $beneficiary->all_grade_1;
                $this->primary_all_grade_2 = (bool) $beneficiary->all_grade_2;
                $this->primary_all_grade_3 = (bool) $beneficiary->all_grade_3;
                $this->primary_severely_wasted = (bool) $beneficiary->severely_wasted;
                $this->primary_wasted = (bool) $beneficiary->wasted;
                $this->primary_normal_weight = (bool) $beneficiary->normal_weight;
                $this->primary_overweight_obese = (bool) $beneficiary->overweight_obese;
                $this->primary_severely_stunted = (bool) $beneficiary->severely_stunted;
                $this->primary_stunted = (bool) $beneficiary->stunted;
                $this->primary_normal_height = (bool) $beneficiary->normal_height;
                $this->primary_tall = (bool) $beneficiary->tall;
                $this->primary_4ps = (bool) $beneficiary->_4ps;
                $this->primary_ip = (bool) $beneficiary->ip;
                $this->primary_pardo = (bool) $beneficiary->pardo;
            } elseif ($beneficiary->name == 'Secondary') {
                $this->secondary_name = $beneficiary->name;
                $this->secondary_all_kinder = (bool) $beneficiary->all_kinder;
                $this->secondary_all_grade_1 = (bool) $beneficiary->all_grade_1;
                $this->secondary_all_grade_2 = (bool) $beneficiary->all_grade_2;
                $this->secondary_all_grade_3 = (bool) $beneficiary->all_grade_3;
                $this->secondary_severely_wasted = (bool) $beneficiary->severely_wasted;
                $this->secondary_wasted = (bool) $beneficiary->wasted;
                $this->secondary_normal_weight = (bool) $beneficiary->normal_weight;
                $this->secondary_overweight_obese = (bool) $beneficiary->overweight_obese;
                $this->secondary_severely_stunted = (bool) $beneficiary->severely_stunted;
                $this->secondary_stunted = (bool) $beneficiary->stunted;
                $this->secondary_normal_height = (bool) $beneficiary->normal_height;
                $this->secondary_tall = (bool) $beneficiary->tall;
                $this->secondary_4ps = (bool) $beneficiary->_4ps;
                $this->secondary_ip = (bool) $beneficiary->ip;
                $this->secondary_pardo = (bool) $beneficiary->pardo;
            }
        }
    }

    public function save()
    {
        $primary = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();
        if ($primary) {
            $primary->update([
                'name' => $this->primary_name ?? $primary->name,
                'all_kinder' => (bool) $this->primary_all_kinder,
                'all_grade_1' => (bool) $this->primary_all_grade_1,
                'all_grade_2' => (bool) $this->primary_all_grade_2,
                'all_grade_3' => (bool) $this->primary_all_grade_3,
                'severely_wasted' => (bool) $this->primary_severely_wasted,
                'wasted' => (bool) $this->primary_wasted,
                'normal_weight' => (bool) $this->primary_normal_weight,
                'overweight_obese' => (bool) $this->primary_overweight_obese,
                'severely_stunted' => (bool) $this->primary_severely_stunted,
                'stunted' => (bool) $this->primary_stunted,
                'normal_height' => (bool) $this->primary_normal_height,
                'tall' => (bool) $this->primary_tall,
                'ip' => (bool) $this->primary_ip,
                'pardo' => (bool) $this->primary_pardo,
            ]);
            // Ensure the leading-underscore column is set explicitly
            $primary->setAttribute('_4ps', (bool) $this->primary_4ps);
            $primary->save();
        }

        $secondary = PrimarySecondaryBeneficiaries::where('name', 'Secondary')->first();
        if ($secondary) {
            $secondarySave = $secondary->update([
                'name' => $this->secondary_name ?? $secondary->name,
                'all_kinder' => (bool) $this->secondary_all_kinder,
                'all_grade_1' => (bool) $this->secondary_all_grade_1,
                'all_grade_2' => (bool) $this->secondary_all_grade_2,
                'all_grade_3' => (bool) $this->secondary_all_grade_3,
                'severely_wasted' => (bool) $this->secondary_severely_wasted,
                'wasted' => (bool) $this->secondary_wasted,
                'normal_weight' => (bool) $this->secondary_normal_weight,
                'overweight_obese' => (bool) $this->secondary_overweight_obese,
                'severely_stunted' => (bool) $this->secondary_severely_stunted,
                'stunted' => (bool) $this->secondary_stunted,
                'normal_height' => (bool) $this->secondary_normal_height,
                'tall' => (bool) $this->secondary_tall,
                'ip' => (bool) $this->secondary_ip,
                'pardo' => (bool) $this->secondary_pardo,
            ]);
            // Ensure the leading-underscore column is set explicitly
            $secondary->setAttribute('_4ps', (bool) $this->secondary_4ps);
            $secondary->save();

            if ($secondarySave) {
                $this->dispatch('primary_secondary_saved');
            }
        }
        if ($secondarySave) {
            $this->dispatch('beneficiaries-saved', ['message' => 'Beneficiaries saved.']);
        }
    }

    public function render()
    {
        return view('livewire.edit-beneficiaries.beneficiaries-selection');
    }
}
