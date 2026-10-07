<?php

namespace App\Livewire\EditBeneficiaries;

use App\Models\Beneficiaries;
use App\Models\NutritionalStatus;
use App\Models\PrimarySecondaryBeneficiaries;
use App\Models\SwappedPupils;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class BeneficiariesList extends Component
{
    use WithPagination;

    public $setBeneficiaries = false;

    public $search = '';

    // the search that was last applied with the Search button
    public $appliedSearch = '';

    private const PER_PAGE = 15;

    // Swap modal state
    public $showSwapModal = false;

    public $swapFromId = null;

    public $swapCandidates = [];

    public $swapCandidatesTruncated = false;

    public $swapSelectedTo = null;

    public $swapReason = null;

    public $swapSearch = '';

    public $swapError = null;

    private const SWAP_CANDIDATE_LIMIT = 100;

    public function mount()
    {
        $this->setBeneficiaries = true;
    }

    public function searchBeneficiaries()
    {
        $this->appliedSearch = trim((string) $this->search);
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->appliedSearch = '';
        $this->resetPage();
    }

    /** Kinder first, then Grade 1 to 12, then non-graded; within a grade by section and name. */
    private function orderByGrade($query)
    {
        return $query
            ->orderByRaw("CASE LOWER(grade)
                WHEN 'k' THEN 0 WHEN 'kinder' THEN 0 WHEN 'kindergarten' THEN 0
                WHEN '1' THEN 1 WHEN '2' THEN 2 WHEN '3' THEN 3 WHEN '4' THEN 4 WHEN '5' THEN 5 WHEN '6' THEN 6
                WHEN '7' THEN 7 WHEN '8' THEN 8 WHEN '9' THEN 9 WHEN '10' THEN 10 WHEN '11' THEN 11 WHEN '12' THEN 12
                WHEN 'non_graded' THEN 13
                ELSE 14
            END")
            ->orderBy('section')
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    private function beneficiariesPage()
    {
        $query = NutritionalStatus::query()->where('isBeneficiary', true);

        if ($this->appliedSearch !== '') {
            $s = '%'.$this->appliedSearch.'%';
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', $s)
                    ->orWhere('grade', 'like', $s)
                    ->orWhere('section', 'like', $s);
            });
        }

        return $this->orderByGrade($query)->paginate(self::PER_PAGE);
    }

    public function render()
    {
        $beneficiaries = $this->beneficiariesPage();

        // e.g. the last pupil of the last page was swapped out: go back to the new last page
        if ($beneficiaries->isEmpty() && $beneficiaries->currentPage() > 1) {
            $this->setPage($beneficiaries->lastPage());
            $beneficiaries = $this->beneficiariesPage();
        }

        return view('livewire.edit-beneficiaries.beneficiaries-list', ['beneficiaries' => $beneficiaries]);
    }

    public function openSwapModal($fromPupilId)
    {
        $this->swapFromId = $fromPupilId;
        $this->swapSelectedTo = null;
        $this->swapReason = null;
        $this->swapError = null;
        // load candidates (initially unfiltered)
        $this->searchSwapCandidates();
        $this->showSwapModal = true;
    }

    public function searchSwapCandidates()
    {
        $this->swapSelectedTo = null;
        $this->swapReason = null;
        $this->swapError = null;

        $query = NutritionalStatus::query()->where('isBeneficiary', false);

        if ($this->swapSearch && trim($this->swapSearch) !== '') {
            $s = '%'.trim($this->swapSearch).'%';
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', $s)
                    ->orWhere('grade', 'like', $s)
                    ->orWhere('section', 'like', $s);
            });
        }

        $this->orderByGrade($query);

        $candidates = $query
            ->select(['id', 'full_name', 'grade', 'section'])
            ->limit(self::SWAP_CANDIDATE_LIMIT + 1)
            ->get();

        $this->swapCandidatesTruncated = $candidates->count() > self::SWAP_CANDIDATE_LIMIT;
        $this->swapCandidates = $candidates->take(self::SWAP_CANDIDATE_LIMIT);
    }

    public function clearSwapSearch()
    {
        $this->swapSearch = '';
        $this->swapSelectedTo = null;
        $this->swapReason = null;
        $this->swapError = null;
        $this->searchSwapCandidates();
    }

    public function closeSwapModal()
    {
        $this->showSwapModal = false;
        $this->swapFromId = null;
        $this->swapCandidates = [];
        $this->swapCandidatesTruncated = false;
        $this->swapSelectedTo = null;
        $this->swapReason = null;
        $this->swapError = null;
    }

    public function applySwap($toId)
    {
        $this->swapError = null;

        if (! $this->swapFromId || ! $toId) {
            $this->showSwapError('Please select a valid replacement pupil.');

            return;
        }

        if ($this->swapFromId == $toId) {
            $this->showSwapError('The replacement must be a different pupil.');

            return;
        }

        try {
            DB::transaction(function () use ($toId) {
                $from = NutritionalStatus::where('id', $this->swapFromId)->lockForUpdate()->first();
                $to = NutritionalStatus::where('id', $toId)->lockForUpdate()->first();

                if (! $from || ! $to) {
                    throw new DomainException('One of the pupil records no longer exists.');
                }

                if (! $from->isBeneficiary) {
                    throw new DomainException('The selected pupil is no longer a beneficiary.');
                }

                if ($to->isBeneficiary) {
                    throw new DomainException('The replacement pupil is already a beneficiary.');
                }

                if (SwappedPupils::where('old_pupil_id', $from->id)->exists()) {
                    throw new DomainException('This beneficiary has already been replaced before.');
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
            });
        } catch (DomainException $e) {
            $this->showSwapError('Swap failed: '.$e->getMessage());

            return;
        } catch (Throwable $e) {
            Log::warning('Beneficiary swap failed.', [
                'from_pupil_id' => $this->swapFromId,
                'to_pupil_id' => $toId,
                'error' => $e->getMessage(),
            ]);

            $this->showSwapError('The swap could not be saved. Please try again.');

            return;
        }

        session()->flash('success', 'Swap saved');
        $this->dispatch('swapped-success', message: 'Pupil swap completed successfully');
        $this->closeSwapModal();
    }

    private function showSwapError(string $message): void
    {
        $this->swapError = $message;
        $this->dispatch('swapped-error', message: $message);
    }

    #[On('primary_secondary_saved')]
    public function getBeneficiaries()
    {
        // reset in every state; the re-selection below is mirrored back to the other states
        NutritionalStatus::withoutGlobalScope('survey_state')->update(['isBeneficiary' => false]);

        $beneficiariesCount = Beneficiaries::first();
        $primary = PrimarySecondaryBeneficiaries::where('name', 'Primary')->first();

        // setting beneficiaries as a collection with no value
        $beneficiaries = NutritionalStatus::where('grade', '100')->get();
        $remaining = $beneficiariesCount->beneficiaries_count;

        if ($primary->all_kinder == true) {
            $allKinderBeneficiaries = NutritionalStatus::where('grade', '=', 'k')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allKinderBeneficiaries);
            $remaining = $remaining - $allKinderBeneficiaries->count();
        }
        Log::info($remaining.': remaining k before grade-based primary selection');
        if ($primary->all_grade_1 == true) {
            $allGrade1 = NutritionalStatus::where('grade', '=', '1')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allGrade1);
            $remaining = $remaining - $allGrade1->count();
        }
        Log::info($remaining.': remaining 1 before grade-based primary selection');
        if ($primary->all_grade_2 == true) {
            $allGrade2 = NutritionalStatus::where('grade', '=', '2')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allGrade2);
            $remaining = $remaining - $allGrade2->count();
        }

        if ($primary->all_grade_3 == true) {
            $allGrade3 = NutritionalStatus::where('grade', '=', '3')
                ->limit($remaining)
                ->get();

            $beneficiaries = $beneficiaries->merge($allGrade3);
            $remaining = $remaining - $allGrade3->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {
            // get primary beneficiaries
            $primarybeneficiaries = NutritionalStatus::whereIn('nutritional_status', [
                'Severely Wasted',
                'Wasted',
            ])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->whereIn('grade', ['k', '1', '2', '3', '4', '5', '6', 'non_graded'])
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
            $remaining = $remaining - $primarybeneficiaries->count();

        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $remaining = $beneficiariesCount->beneficiaries_count - $beneficiaries->count();

            // Severely stunted / stunted pupils of any weight status (wasted ones are already selected above)
            $phase1 = NutritionalStatus::whereIn('height_for_age', ['Severely Stunted', 'Stunted'])
                ->whereNotIn('id', $beneficiaries->pluck('id'))
                ->whereIn('grade', ['k', '1', '2', '3', '4', '5', '6', 'non_graded'])
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
            $remaining = $remaining - $phase1->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $phase2 = NutritionalStatus::where('nutritional_status', 'Normal')
                ->whereIn('height_for_age', ['Normal', 'Tall'])
                ->whereIn('grade', ['k', '1', '2', '3', '4', '5', '6', 'non_graded'])
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
            $remaining = $remaining - $phase2->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {

            $phase3 = NutritionalStatus::whereIn('nutritional_status', ['normal'])
                ->whereIn('grade', ['k', '1', '2', '3', '4', '5', '6', 'non_graded'])
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
            $remaining = $remaining - $phase3->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {
            $phase4 = NutritionalStatus::whereIn('height_for_age', ['normal', 'tall'])
                ->whereIn('grade', ['k', '1', '2', '3', '4', '5', '6', 'non_graded'])
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
            $remaining = $remaining - $phase4->count();
        }

        if ($beneficiariesCount->beneficiaries_count > $beneficiaries->count()) {
            $phase5 = NutritionalStatus::whereIn('nutritional_status', ['overweight'])
                ->whereIn('grade', ['k', '1', '2', '3', '4', '5', '6', 'non_graded'])
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
            $remaining = $remaining - $phase5->count();
        }

        $setBeneficiariesLocal = $beneficiaries->each(function ($beneficiary) {
            $beneficiary->isBeneficiary = true;
            $beneficiary->save();
        });

        if ($setBeneficiariesLocal) {
            $this->resetPage();
            $this->setBeneficiaries = true;
        } else {
            $this->setBeneficiaries = false;
        }
    }
}
