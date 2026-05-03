<?php

namespace App\Livewire\EditBeneficiaries;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\PrimarySecondaryBeneficiaries;
use App\Models\Beneficiaries;
use App\Models\NutritionalStatus;
use App\Models\SwappedPupils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BeneficiariesList extends Component
{
    public $beneficiaries;
    public $setBeneficiaries = false;
    public $search = '';
    // Swap modal state
    public $showSwapModal = false;
    public $swapFromId = null;
    public $swapCandidates = [];
    public $swapSelectedTo = null;
    public $swapReason = null;
    public $swapSearch = '';
    public function mount()
    {
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

    public function getBeneficiearies()
    {
        $this->beneficiaries = NutritionalStatus::where('isBeneficiary', "=", true)->get();
        $this->setBeneficiaries = true;
    }

    public function render()
    {
        return view('livewire.edit-beneficiaries.beneficiaries-list');
    }

    public function openSwapModal($fromPupilId)
    {
        $this->swapFromId = $fromPupilId;
        // load candidates (initially unfiltered)
        $this->searchSwapCandidates();
        $this->showSwapModal = true;
    }

    public function searchSwapCandidates()
    {
        $query = NutritionalStatus::query()->where('isBeneficiary', false);

        if ($this->swapSearch && trim($this->swapSearch) !== '') {
            $s = '%' . trim($this->swapSearch) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', $s)
                    ->orWhere('grade', 'like', $s)
                    ->orWhere('section', 'like', $s);
            });
        }

        $query->orderByRaw("\n            CASE\n                WHEN grade = 'k' THEN 0\n                WHEN grade = '1' THEN 1\n                WHEN grade = '2' THEN 2\n                WHEN grade = '3' THEN 3\n                WHEN grade = '4' THEN 4\n                WHEN grade = '5' THEN 5\n                WHEN grade = '6' THEN 6\n                WHEN grade = '7' THEN 7\n                WHEN grade = '8' THEN 8\n                WHEN grade = '9' THEN 9\n                WHEN grade = '10' THEN 10\n                WHEN grade = '11' THEN 11\n                WHEN grade = '12' THEN 12\n                WHEN grade = 'non_graded' THEN 13\n                ELSE 14\n            END\n        ");

        $this->swapCandidates = $query->limit(2000)->get();
    }

    public function clearSwapSearch()
    {
        $this->swapSearch = '';
        $this->searchSwapCandidates();
    }

    public function closeSwapModal()
    {
        $this->showSwapModal = false;
        $this->swapFromId = null;
        $this->swapCandidates = [];
        $this->swapSelectedTo = null;
    }

    public function applySwap($toId)
    {
        if (!$this->swapFromId || !$toId) {
            session()->flash('error', 'Invalid selection');
            return;
        }

        if ($this->swapFromId == $toId) {
            session()->flash('error', 'Replacement must be different');
            return;
        }

        DB::beginTransaction();
        try {
            $from = NutritionalStatus::where('id', $this->swapFromId)->lockForUpdate()->first();
            $to = NutritionalStatus::where('id', $toId)->lockForUpdate()->first();

            if (!$from || !$to) {
                throw new \Exception('Pupil record missing');
            }

            if (!$from->isBeneficiary) {
                throw new \Exception('Source pupil is not a beneficiary');
            }

            if ($to->isBeneficiary) {
                throw new \Exception('Target pupil is already a beneficiary');
            }

            $from->isBeneficiary = false;
            $from->save();

            $to->isBeneficiary = true;
            $to->save();

            SwappedPupils::create([
                'old_pupil_id' => $from->id,
                'new_pupil_id' => $to->id,
                'reason' => $this->swapReason,
                'swap_date' => now(),
            ]);

            DB::commit();
            session()->flash('success', 'Swap saved');
            $this->dispatch('swapped-success', ['message' => 'Pupil swap completed successfully']);
            $this->closeSwapModal();
            $this->getBeneficiearies();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Swap failed: ' . $e->getMessage());
        }
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

        if ($primary->all_kinder == true) {
            $allKinderBeneficiaries = NutritionalStatus::where('grade', '=', 'k')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allKinderBeneficiaries);
            $remaining = $beneficiariesCount->beneficiaries_count - $allKinderBeneficiaries->count();
        }

        if ($primary->all_grade_1 == true) {
            $allGrade1 = NutritionalStatus::where('grade', '=', '1')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allGrade1);
            $remaining = $beneficiariesCount->beneficiaries_count - $allGrade1->count();
        }

        if ($primary->all_grade_2 == true) {
            $allGrade2 = NutritionalStatus::where('grade', '=', '2')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allGrade2);
            $remaining = $beneficiariesCount->beneficiaries_count - $allGrade2->count();
        }

        if ($primary->all_grade_3 == true) {
            $allGrade3 = NutritionalStatus::where('grade', '=', '3')
                ->limit($remaining)
                ->get();

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
                ->whereIn('grade', ['k','1','2','3','4','5','6','non_graded'])
                ->limit($remaining)
                ->orderByRaw("
                    CASE
                        WHEN grade = 'k' THEN 0
                        WHEN grade = '1' THEN 1
                        WHEN grade = '2' THEN 2
                        WHEN grade = '3' THEN 3
                        WHEN grade = '4' THEN 4
                        WHEN grade = '5' THEN 5
                        WHEN grade = '6' THEN 6
                        ELSE 'non_graded'
                    END
                ")
                ->get();

            $beneficiaries = $beneficiaries->merge($primarybeneficiaries);
            $remaining = $beneficiariesCount->beneficiaries_count - $primarybeneficiaries->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

            $phase1 = NutritionalStatus::where('nutritional_status', 'Normal')
                ->whereIn('height_for_age', ['Severely Stunted', 'Stunted'])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->whereIn('grade', ['k','1','2','3','4','5','6','non_graded'])
                ->orderByRaw("
                    CASE
                        WHEN grade = 'k' THEN 0
                        WHEN grade = '1' THEN 1
                        WHEN grade = '2' THEN 2
                        WHEN grade = '3' THEN 3
                        WHEN grade = '4' THEN 4
                        WHEN grade = '5' THEN 5
                        WHEN grade = '6' THEN 6
                        ELSE 'non_graded'
                    END
                ")
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($phase1);
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

            $phase2 = NutritionalStatus::where('nutritional_status', 'Normal')
                ->whereIn('height_for_age', ['Normal', 'Tall'])
                ->whereIn('grade', ['k','1','2','3','4','5','6','non_graded'])
                ->where(function ($q) {
                    $q->where('_4ps', 1)
                        ->orWhere('ip', 1)
                        ->orWhere('pardo', 1);
                })
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->orderByRaw("
                    CASE
                        WHEN grade = 'k' THEN 0
                        WHEN grade = '1' THEN 1
                        WHEN grade = '2' THEN 2
                        WHEN grade = '3' THEN 3
                        WHEN grade = '4' THEN 4
                        WHEN grade = '5' THEN 5
                        WHEN grade = '6' THEN 6
                        ELSE 'non_graded'
                    END
                ")
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($phase2);
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

            $phase3 = NutritionalStatus::whereIn('nutritional_status', ['normal'])
                ->whereIn('grade', ['k','1','2','3','4','5','6','non_graded'])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->orderByRaw("
                    CASE
                        WHEN grade = 'k' THEN 0
                        WHEN grade = '1' THEN 1
                        WHEN grade = '2' THEN 2
                        WHEN grade = '3' THEN 3
                        WHEN grade = '4' THEN 4
                        WHEN grade = '5' THEN 5
                        WHEN grade = '6' THEN 6
                        WHEN grade = 'non_graded' THEN 7
                        ELSE 8
                    END
                ")
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($phase3);
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

            $phase4 = NutritionalStatus::whereIn('height_for_age', ['normal', 'tall'])
                ->whereIn('grade', ['k','1','2','3','4','5','6','non_graded'])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->orderByRaw("
                    CASE
                        WHEN grade = 'k' THEN 0
                        WHEN grade = '1' THEN 1
                        WHEN grade = '2' THEN 2
                        WHEN grade = '3' THEN 3
                        WHEN grade = '4' THEN 4
                        WHEN grade = '5' THEN 5
                        WHEN grade = '6' THEN 6
                        WHEN grade = 'non_graded' THEN 7
                        ELSE 8
                    END
                ")
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($phase4);
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

            $phase5 = NutritionalStatus::whereIn('nutritional_status', ['overweight'])
                ->whereIn('grade', ['k','1','2','3','4','5','6','non_graded'])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->orderByRaw("
                    CASE
                        WHEN grade = 'k' THEN 0
                        WHEN grade = '1' THEN 1
                        WHEN grade = '2' THEN 2
                        WHEN grade = '3' THEN 3
                        WHEN grade = '4' THEN 4
                        WHEN grade = '5' THEN 5
                        WHEN grade = '6' THEN 6
                        WHEN grade = 'non_graded' THEN 7
                        ELSE 8
                    END
                ")
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($phase5);
        }

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
