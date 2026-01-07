<?php

namespace App\Livewire\Dashboard\Pupils;

use Livewire\Component;
use Livewire\Attributes\On;

class AddedLast extends Component
{
    public $name;

    #[On('pupil-saved')]
    public function setAddedLast($name) {
        $this->name = $name;
    }
    public function render()
    {
        return view('livewire.dashboard.pupils.added-last');
    }
}
