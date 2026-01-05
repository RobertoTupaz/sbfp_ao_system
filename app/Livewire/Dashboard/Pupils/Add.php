<?php

namespace App\Livewire\Dashboard\Pupils;

use Livewire\Component;

class Add extends Component
{
    public $name;
    public $date_of_birth;
    public $date_of_weighing;
    public $weight;
    public $height;
    public $sex;
    public $age_years_months;
    public $age_years;
    public $age_months;
    public $bmi;
    public $nutritional_status;
    public $height_for_age;

    protected $rules = [
        'name' => 'required|string|max:255',
        'date_of_birth' => 'nullable|date',
        'date_of_weighing' => 'nullable|date',
        'weight' => 'nullable|numeric',
        'height' => 'nullable|numeric',
        'sex' => 'nullable|string',
        'age_years' => 'nullable|integer',
        'age_months' => 'nullable|integer',
        'bmi' => 'nullable|numeric',
        'nutritional_status' => 'nullable|string',
        'height_for_age' => 'nullable|string',
    ];

    public function mount()
    {
        $this->date_of_weighing = date('Y-m-d');
    }

    public function savePupil()
    {
        $this->validate();

        // For now, just flash success and reset fields
        session()->flash('success', 'Pupil saved (stub)');
        $this->reset(['name','date_of_birth','date_of_weighing','weight','height','sex','age_years_months','age_years','age_months','bmi','nutritional_status','height_for_age']);
        // reset date_of_weighing to today
        $this->date_of_weighing = date('Y-m-d');
    }

    public function render()
    {
        return view('livewire.dashboard.pupils.add');
    }
}
