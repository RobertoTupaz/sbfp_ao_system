<?php

namespace App\Livewire\EditBeneficiaries;

use App\Models\HfaSimplifiedVersion;
use App\Models\NutritionalStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class EditPupil extends Component
{
    public $id;
    public $first_name;
    public $last_name;
    public $suffix_name;
    public $date_of_birth;
    public $date_of_weighing;
    public $weight;
    public $height;
    public $sex;
    public $grade;
    public $section;
    public $age_years_months;
    public $age_years;
    public $age_months;
    public $bmi;
    public $nutritional_status;
    public $height_for_age;
    public $fourps = false;
    public $ip = false;
    public $pardo = false;
    public $dewormed = false;
    public $parent_consent_milk = false;
    public $sbfp_previous_beneficiary = false;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'suffix_name' => 'nullable|string|max:100',
        'date_of_birth' => 'required|date',
        'date_of_weighing' => 'nullable|date',
        'weight' => 'required|numeric',
        'height' => 'required|numeric',
        'sex' => 'required|string',
        'grade' => 'required|string|max:100',
        'section' => 'required|string|max:100',
        'age_years' => 'nullable|integer',
        'age_months' => 'nullable|integer',
        'bmi' => 'nullable|numeric',
        'nutritional_status' => 'nullable|string',
        'height_for_age' => 'nullable|string',
        'fourps' => 'boolean',
        'ip' => 'boolean',
        'pardo' => 'boolean',
        'dewormed' => 'boolean',
        'parent_consent_milk' => 'boolean',
        'sbfp_previous_beneficiary' => 'boolean',
    ];

    public function mount($pupil)
    {
        $this->id = $pupil->id;
        $this->first_name = $pupil->first_name;
        $this->last_name = $pupil->last_name;
        $this->date_of_weighing = Carbon::parse($pupil->date_of_weighing)->toDateString();
        $this->suffix_name = $pupil->suffix_name;
        $this->date_of_birth = Carbon::parse($pupil->birthday)->toDateString();
        log::info('DOW: ' . $this->date_of_weighing . ' ' . $this->first_name . ' DOB: ' . $this->date_of_birth . ' DOB :' . $pupil->birthday);
        $this->weight = $pupil->weight;
        $this->height = $pupil->height;
        $this->sex = strtolower($pupil->sex);
        $this->grade = $pupil->grade;
        $this->section = $pupil->section;
        $this->age_years_months = $pupil->age_years_months;
        $this->age_years = $pupil->age_years;
        $this->age_months = $pupil->age_months;
        $this->bmi = $pupil->bmi;
        $this->nutritional_status = $pupil->nutritional_status;
        $this->height_for_age = $pupil->height_for_age;
        $this->fourps = (bool)$pupil->_4ps;
        $this->ip = (bool)$pupil->ip;
        $this->pardo = (bool)$pupil->pardo;
        $this->dewormed = (bool)$pupil->dewormed;
        $this->parent_consent_milk = (bool)$pupil->parent_consent_milk;
        $this->sbfp_previous_beneficiary = (bool)$pupil->sbfp_previous_beneficiary;
    }

    public function getHFA()
    {
        $ageInMonths = $this->age_months + ($this->age_years * 12);
        $height    = $this->height;
        $gender    = $this->sex == 'm' ? 'male' : 'female';

        Log::info($ageInMonths . ' ' . $height . ' ' . $gender);
        $hfa = HfaSimplifiedVersion::where('month', $ageInMonths)
            ->where('gender', $gender)
            ->first();

        Log::info($hfa);
        if (!$hfa) {
            return;
        }

        $status = "";

        if ($height < $hfa->less_negative_3sd) {
            $status = 'Severely Stunted';
        } elseif ($height <= $hfa->to_less_negative_2sd) {
            $status = 'Stunted';
        } elseif ($height <= $hfa->to_positive_2sd) {
            $status = 'Normal';
        } else {
            $status = 'Tall';
        }
        Log::info('HFA Status: ' . $status);
        $this->height_for_age = $status;
    }

    public function savePupil()
    {
        $this->validate();

        // compute age_years and age_months from birthday + weighing date when possible
        if ($this->date_of_birth) {
            $weighDate = $this->date_of_weighing ? Carbon::parse($this->date_of_weighing) : Carbon::now();
            $dob = Carbon::parse($this->date_of_birth);
            $years = $dob->diffInYears($weighDate);
            $months = $dob->diffInMonths($weighDate) - ($years * 12);
            $this->age_years = $years;
            $this->age_months = $months;
        }

        $fullName = trim("{$this->last_name}, {$this->first_name} {$this->suffix_name}");
        $fullName = preg_replace('/,\s*$/', '', $fullName);

        $data = [
            'full_name' => $fullName,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'suffix_name' => $this->suffix_name ?: null,
            'birthday' => $this->date_of_birth ?: null,
            'sex' => $this->sex ?: null,
            'weight' => $this->weight ?: null,
            'height' => $this->height ?: null,
            'grade' => trim($this->grade),
            'section' => trim($this->section),
            'age_years' => $this->age_years ?: null,
            'age_months' => $this->age_months ?: null,
            'bmi' => $this->bmi ?: null,
            'nutritional_status' => $this->nutritional_status ?: null,
            'height_for_age' => $this->height_for_age ?: null,
            'date_of_weighing' => $this->date_of_weighing ?: null,
            '_4ps' => $this->fourps ? 1 : 0,
            'ip' => $this->ip ? 1 : 0,
            'pardo' => $this->pardo ? 1 : 0,
            'dewormed' => $this->dewormed ? 1 : 0,
            'parent_consent_milk' => $this->parent_consent_milk ? 1 : 0,
            'sbfp_previous_beneficiary' => $this->sbfp_previous_beneficiary ? 1 : 0,
        ];

        if ($this->id) {
            NutritionalStatus::where('id', $this->id)->update($data);
            $this->dispatch('pupil-saved', $fullName);
            session()->flash('success', 'Pupil updated');
        }
    }

    public function render()
    {
        return view('livewire.edit-beneficiaries.edit-pupil');
    }
}
