<?php

namespace App\Livewire\Dashboard\Pupils;

use App\Models\NutritionalStatus;
use App\Support\NutritionalCalculator;
use App\Support\SurveyState;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Midline: copies weight/height from baseline. Endline: copies them from midline.
 * Every state has the same pupils, so this only fills in the measurements.
 */
class CopyMeasurements extends Component
{
    public string $state = SurveyState::BASELINE;

    public ?string $source = null;

    public function mount(): void
    {
        $this->state = SurveyState::current();
        $this->source = SurveyState::previous($this->state);
    }

    public function copy()
    {
        if (! $this->source) {
            return;
        }

        @set_time_limit(300);

        // pupils added before states shared one roster are added to the states that lack them
        NutritionalStatus::syncRoster();

        $sources = NutritionalStatus::withoutGlobalScope('survey_state')
            ->where('survey_state', $this->source)
            ->get();

        if ($sources->isEmpty()) {
            session()->flash('copy_error', 'There is no '.$this->source.' data to copy yet.');

            return;
        }

        $targets = NutritionalStatus::all()->keyBy('pupil_key');
        $updated = 0;

        DB::transaction(function () use ($sources, $targets, &$updated) {
            foreach ($sources as $source) {
                $target = $targets->get($source->pupil_key);

                if (! $target) {
                    continue;
                }

                $target->update($this->measurements($source, $target));
                $updated++;
            }
        });

        session()->flash('copy_message', "Copied {$this->source} weight and height of {$updated} pupils to {$this->state}.");

        return $this->redirect(url()->previous());
    }

    /** Weight/height from the source pupil, with age and nutritional status recalculated. */
    protected function measurements(NutritionalStatus $source, NutritionalStatus $target): array
    {
        $ageYears = $target->age_years;
        $ageMonths = $target->age_months;

        if ($target->birthday) {
            $now = Carbon::now();
            $ageYears = $target->birthday->diffInYears($now);
            $ageMonths = $target->birthday->copy()->addYears($ageYears)->diffInMonths($now);
        }

        $classified = NutritionalCalculator::classify(
            $source->weight,
            $source->height,
            ((int) $ageYears * 12) + (int) $ageMonths,
            $target->sex,
        );

        return [
            'weight' => $source->weight,
            'height' => $source->height,
            'age_years' => $ageYears,
            'age_months' => $ageMonths,
        ] + $classified;
    }

    public function render()
    {
        return view('livewire.dashboard.pupils.copy-measurements');
    }
}
