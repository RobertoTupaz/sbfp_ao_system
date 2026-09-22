<?php

namespace App\Livewire\RetainDeleted;

use App\Models\NutritionalStatus;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Base extends Component
{
    use WithPagination;

    public $search = '';

    public $grade = '';

    public function mount(): void
    {
        abort_if(auth()->user()->role === 'focal', 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGrade(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'grade']);
        $this->resetPage();
    }

    public function restorePupil(int $pupilId): void
    {
        $pupil = NutritionalStatus::onlyTrashed()->find($pupilId);

        if (! $pupil) {
            session()->flash('error', 'Deleted pupil not found.');

            return;
        }

        DB::transaction(function () use ($pupil) {
            $pupil->deleted_by = null;
            $pupil->restore();
            $pupil->save();
        });

        session()->flash('success', 'Pupil restored to Track Enrollees.');
    }

    public function render()
    {
        $query = NutritionalStatus::onlyTrashed()
            ->with('deletedBy:id,name')
            ->latest('deleted_at');

        $search = trim($this->search);
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where('full_name', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('section', 'like', $like);
            });
        }

        if ($this->grade !== '') {
            $query->where('grade', $this->grade);
        }

        return view('livewire.retain-deleted.base', [
            'deletedPupils' => $query->paginate(15),
        ]);
    }
}
