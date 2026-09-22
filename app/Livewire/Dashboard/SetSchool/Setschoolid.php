<?php

namespace App\Livewire\Dashboard\SetSchool;

use Livewire\Component;
use App\Models\AllSchool;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Setschoolid extends Component
{
    public $school_id;

    protected $rules = [
        'school_id' => 'required|string|max:255',
    ];

    public function mount()
    {
        $user = auth()->user();
        $this->school_id = $user->school_id ?? null;
    }

    public function saveSchool()
    {
        $this->validate();

        $user = auth()->user();
        if (!$user) {
            $this->addError('school_id', 'User not authenticated.');
            return;
        }

        $exists = AllSchool::where('school_id', $this->school_id)->exists();

        if (!$exists) {
            $this->dispatch('swal:error', [
                'message' => 'School ID not found in records. Please verify and try again.'
            ]);
            return;
        }

        $user->school_id = $this->school_id;
        $user->save();

        // $this->dispatch('swal:success', [
        //     'message' => 'School ID saved.'
        // ]);
        $this->notif();

        session()->flash('message', 'School ID saved.');
    }

    public function notif()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }

    public function render()
    {
        return view('livewire.dashboard.set-school.setschoolid');
    }
}
