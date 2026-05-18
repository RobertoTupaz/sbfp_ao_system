<?php

namespace App\Livewire\SchoolProfile;

use App\Models\AllSchool;
use App\Models\SchoolProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Base extends Component
{
    public $school_id = '';
    public $school_head_name = '';
    public $school_focal_name = '';
    public $school_email = '';
    public $schools = [];

    public function mount()
    {
        $this->schools = AllSchool::orderBy('school_name')->get()->toArray();
        $this->school_id = Auth::user()->school_id ?? '';
        $this->loadProfile();
    }

    public function updatedSchoolId()
    {
        $this->loadProfile();
    }

    protected function loadProfile()
    {
        if ($this->school_id) {
            $profile = SchoolProfile::where('school_id', $this->school_id)->first();
            $this->school_head_name = $profile?->school_head_name ?? '';
            $this->school_focal_name = $profile?->school_focal_name ?? '';
            $this->school_email = $profile?->school_email ?? '';
        } else {
            $this->school_head_name = '';
            $this->school_focal_name = '';
            $this->school_email = '';
        }
    }

    public function save()
    {
        $this->validate([
            'school_id' => 'nullable|exists:schools,school_id',
            'school_head_name' => 'nullable|string|max:255',
            'school_focal_name' => 'nullable|string|max:255',
            'school_email' => 'nullable|email|max:255',
        ]);

        $user = Auth::user();
        $user->school_id = $this->school_id ?: null;
        $user->save();

        if ($this->school_id) {
            SchoolProfile::updateOrCreate(
                ['school_id' => $this->school_id],
                [
                    'school_head_name' => $this->school_head_name,
                    'school_focal_name' => $this->school_focal_name,
                    'school_email' => $this->school_email,
                ]
            );
        }

        session()->flash('success', 'School profile saved successfully.');
    }

    public function render()
    {
        return view('livewire.school-profile.base');
    }
}
