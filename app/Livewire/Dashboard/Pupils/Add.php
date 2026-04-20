<?php

namespace App\Livewire\Dashboard\Pupils;

use App\Models\HfaSimplifiedVersion;
use Livewire\Component;
use App\Models\NutritionalStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Add extends Component
{
    public $first_name;
    public $last_name;
    public $suffix_name;
    public $search_lastname = '';
    public $searchResults = [];
    public $editingId = null;
    public $showForm = false;
    public $date_of_birth;
    public $date_of_weighing;
    public $weight;
    public $height;
    public $sex;
    public $grade;
    public $section;
    public $age_years_months = null;
    public $age_years = null;
    public $age_months = null;
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

    public function mount()
    {
        $this->date_of_weighing = date('Y-m-d');
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
        // Remove trailing comma and extra spaces if suffix_name is empty
        $fullName = preg_replace('/,\s*$/', '', $fullName);

        Log::info($this->last_name);
        if ($this->editingId) {
            $record = NutritionalStatus::find($this->editingId);
            if ($record) {
                $record->update([
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
                ]);
            }
        } else {
            $record = NutritionalStatus::create([
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
                // additional flags
                '_4ps' => $this->fourps ? 1 : 0,
                'ip' => $this->ip ? 1 : 0,
                'pardo' => $this->pardo ? 1 : 0,
                'dewormed' => $this->dewormed ? 1 : 0,
                'parent_consent_milk' => $this->parent_consent_milk ? 1 : 0,
                'sbfp_previous_beneficiary' => $this->sbfp_previous_beneficiary ? 1 : 0,
            ]);
        }

        // notify the front-end so the UI can show lastly added pupil
        $this->dispatch('pupil-saved', ['name' => $record->full_name ?? 'Student Lastly added']);
        $this->notif();

        // reset form (clear editing state too)
        $this->editingId = null;
        $this->showForm = false;
        $this->search_lastname = '';
        $this->searchResults = [];
        $this->reset(['first_name', 'last_name', 'suffix_name', 'date_of_birth', 'date_of_weighing', 'weight', 'height', 'sex', 'age_years_months', 'age_years', 'age_months', 'bmi', 'nutritional_status', 'height_for_age', 'fourps', 'ip', 'pardo', 'dewormed', 'parent_consent_milk', 'sbfp_previous_beneficiary']);
        // reset date_of_weighing to today
        $this->date_of_weighing = date('Y-m-d');
    }

    public function searchLastname()
    {
        $term = trim($this->search_lastname);
        if ($term === '') {
            $this->searchResults = [];
            return;
        }
        $this->searchResults = NutritionalStatus::where('last_name', 'like', $term . '%')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(20)
            ->get();
    }

    public function selectExisting($id)
    {
        $record = NutritionalStatus::find($id);
        if (!$record) return;
        $this->editingId = $record->id;
        $this->showForm = true;
        $this->first_name = $record->first_name;
        $this->last_name = $record->last_name;
        $this->suffix_name = $record->suffix_name;
        $this->date_of_birth = $record->birthday;
        $this->date_of_weighing = $record->date_of_weighing ?: date('Y-m-d');
        $this->weight = $record->weight;
        $this->height = $record->height;
        $this->sex = $record->sex;
        $this->grade = $record->grade;
        $this->section = $record->section;
        $this->age_years = $record->age_years;
        $this->age_months = $record->age_months;
        $this->bmi = $record->bmi;
        $this->nutritional_status = $record->nutritional_status;
        $this->height_for_age = $record->height_for_age;
        $this->fourps = (bool) $record->_4ps;
        $this->ip = (bool) $record->ip;
        $this->pardo = (bool) $record->pardo;
        $this->dewormed = (bool) $record->dewormed;
        $this->parent_consent_milk = (bool) $record->parent_consent_milk;
        $this->sbfp_previous_beneficiary = (bool) $record->sbfp_previous_beneficiary;
        $this->searchResults = [];
    }

    public function createNew()
    {
        $this->editingId = null;
        $this->showForm = true;
        $this->searchResults = [];
        $this->search_lastname = '';
        $this->reset(['first_name', 'last_name', 'suffix_name', 'date_of_birth', 'weight', 'height', 'sex', 'age_years_months', 'age_years', 'age_months', 'bmi', 'nutritional_status', 'height_for_age', 'fourps', 'ip', 'pardo', 'dewormed', 'parent_consent_milk', 'sbfp_previous_beneficiary']);
        $this->date_of_weighing = date('Y-m-d');
    }

    public function hideForm()
    {
        $this->editingId = null;
        $this->showForm = false;
        $this->searchResults = [];
        $this->search_lastname = '';
        $this->reset(['first_name', 'last_name', 'suffix_name', 'date_of_birth', 'weight', 'height', 'sex', 'age_years_months', 'age_years', 'age_months', 'bmi', 'nutritional_status', 'height_for_age', 'fourps', 'ip', 'pardo', 'dewormed', 'parent_consent_milk', 'sbfp_previous_beneficiary']);
        $this->date_of_weighing = date('Y-m-d');
    }

    public function notif()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }

    public function getHFA()
    {
        // compute age_years and age_months from birthday + weighing date when possible
        if ($this->date_of_birth) {
            $weighDate = $this->date_of_weighing ? Carbon::parse($this->date_of_weighing) : Carbon::now();
            $dob = Carbon::parse($this->date_of_birth);
            $years = $dob->diffInYears($weighDate);
            $months = $dob->diffInMonths($weighDate) - ($years * 12);
            $this->age_years = $years;
            $this->age_months = $months;
        }

        Log::info($this->age_months . ' ' . $this->age_years);
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

    public function render()
    {
        return view('livewire.dashboard.pupils.add');
    }
}
