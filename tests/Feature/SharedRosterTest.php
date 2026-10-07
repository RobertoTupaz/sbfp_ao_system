<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Pupils\CopyMeasurements;
use App\Livewire\TrackEnrollees\Base as TrackEnrollees;
use App\Models\NutritionalStatus;
use App\Support\NutritionalCalculator;
use App\Support\SurveyState;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Baseline, midline and endline have the same pupils; only the measurements differ.
 * Runs on its own in-memory SQLite database.
 */
class SharedRosterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('nutritional_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('survey_state')->default('baseline');
            $table->string('pupil_key')->nullable();
            $table->string('full_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix_name')->nullable();
            $table->date('birthday')->nullable();
            $table->string('sex')->nullable();
            $table->string('grade')->nullable();
            $table->string('section')->nullable();
            $table->float('weight')->nullable();
            $table->float('height')->nullable();
            $table->integer('age_years')->nullable();
            $table->integer('age_months')->nullable();
            $table->float('bmi')->nullable();
            $table->string('nutritional_status')->nullable();
            $table->string('height_for_age')->nullable();
            $table->date('date_of_weighing')->nullable();
            $table->boolean('_4ps')->default(false);
            $table->boolean('ip')->default(false);
            $table->boolean('pardo')->default(false);
            $table->boolean('dewormed')->default(false);
            $table->boolean('parent_consent_milk')->default(false);
            $table->boolean('sbfp_previous_beneficiary')->default(false);
            $table->boolean('isBeneficiary')->default(false);
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('swapped_pupils', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('old_pupil_id');
            $table->unsignedBigInteger('new_pupil_id');
            $table->string('reason')->nullable();
            $table->date('swap_date')->nullable();
            $table->timestamps();
        });

        Schema::create('bmi_simplefied_version', function (Blueprint $table) {
            $table->id();
            $table->integer('months')->nullable();
            $table->string('sex', 1)->nullable();
            $table->float('sd_minus_3')->nullable();
            $table->float('sd_minus_2')->nullable();
            $table->float('sd_plus_2')->nullable();
            $table->float('sd_plus_3')->nullable();
        });

        Schema::create('hfa_simplefied_version', function (Blueprint $table) {
            $table->id();
            $table->integer('month')->nullable();
            $table->string('gender')->nullable();
            $table->float('less_negative_3sd')->nullable();
            $table->float('to_less_negative_2sd')->nullable();
            $table->float('to_positive_2sd')->nullable();
        });

        NutritionalCalculator::flush();

        // a session, so the survey state can be switched like in the nav
        request()->setLaravelSession(app('session.store'));
        SurveyState::set('baseline');
    }

    private function pupil(array $attributes = []): NutritionalStatus
    {
        return NutritionalStatus::create($attributes + [
            'full_name' => 'Dela Cruz, Ana',
            'first_name' => 'Ana',
            'last_name' => 'Dela Cruz',
            'birthday' => '2015-03-04',
            'sex' => 'f',
            'grade' => '5',
            'section' => 'Sampaguita',
            'weight' => 30.0,
            'height' => 140.0,
        ]);
    }

    private function rows(string $key)
    {
        return NutritionalStatus::withoutGlobalScope('survey_state')->where('pupil_key', $key)->get()->keyBy('survey_state');
    }

    public function test_a_new_pupil_exists_in_every_state_without_measurements_in_the_others(): void
    {
        SurveyState::set('midline');
        $pupil = $this->pupil();

        $rows = $this->rows($pupil->pupil_key);

        $this->assertEqualsCanonicalizing(['baseline', 'midline', 'endline'], $rows->keys()->all());
        $this->assertSame(30.0, $rows['midline']->weight);
        $this->assertNull($rows['baseline']->weight);
        $this->assertNull($rows['endline']->height);
        $this->assertSame('Sampaguita', $rows['baseline']->section);
        $this->assertSame('Dela Cruz, Ana', $rows['endline']->full_name);
    }

    public function test_roster_details_follow_the_pupil_but_measurements_stay_in_their_state(): void
    {
        $pupil = $this->pupil();

        $pupil->update(['grade' => '6', 'last_name' => 'Reyes', '_4ps' => true, 'isBeneficiary' => true, 'weight' => 31.5]);

        $rows = $this->rows($pupil->pupil_key);
        foreach (['baseline', 'midline', 'endline'] as $state) {
            $this->assertSame('6', $rows[$state]->grade, $state);
            $this->assertSame('Reyes', $rows[$state]->last_name, $state);
            $this->assertTrue($rows[$state]->_4ps, $state);
            $this->assertTrue($rows[$state]->isBeneficiary, $state);
        }
        $this->assertSame(31.5, $rows['baseline']->weight);
        $this->assertNull($rows['midline']->weight);
    }

    public function test_deleting_and_restoring_a_pupil_applies_to_every_state(): void
    {
        $pupil = $this->pupil();
        $key = $pupil->pupil_key;

        $pupil->delete();

        $this->assertSame(0, NutritionalStatus::withoutGlobalScope('survey_state')->where('pupil_key', $key)->count());
        $this->assertSame(3, NutritionalStatus::withoutGlobalScope('survey_state')->onlyTrashed()->where('pupil_key', $key)->count());

        NutritionalStatus::onlyTrashed()->find($pupil->id)->restore();

        $this->assertSame(3, NutritionalStatus::withoutGlobalScope('survey_state')->where('pupil_key', $key)->count());
        $this->assertSame(0, NutritionalStatus::withoutGlobalScope('survey_state')->onlyTrashed()->where('pupil_key', $key)->count());
    }

    public function test_permanently_deleting_a_pupil_removes_every_state(): void
    {
        $pupil = $this->pupil();

        $pupil->forceDelete();

        $this->assertSame(0, NutritionalStatus::withoutGlobalScope('survey_state')->withTrashed()->count());
    }

    public function test_bulk_created_pupils_are_added_to_every_state_in_one_sync(): void
    {
        NutritionalStatus::withoutRosterSync(function () {
            foreach (range(1, 5) as $i) {
                $this->pupil(['first_name' => "Pupil {$i}", 'full_name' => "Pupil {$i}"]);
            }
        });

        foreach (['baseline', 'midline', 'endline'] as $state) {
            $this->assertSame(5, NutritionalStatus::withoutGlobalScope('survey_state')->where('survey_state', $state)->count(), $state);
        }

        // nothing left to add
        $this->assertSame(0, NutritionalStatus::syncRoster());
    }

    public function test_syncing_fills_in_pupils_that_only_exist_in_some_states_but_not_deleted_ones(): void
    {
        // a pupil saved before states shared one roster: it only has a midline row
        NutritionalStatus::create(['survey_state' => 'midline', 'pupil_key' => 'partial', 'first_name' => 'Partial', 'grade' => '2']);
        NutritionalStatus::withoutGlobalScope('survey_state')->withTrashed()->where('pupil_key', 'partial')->where('survey_state', '!=', 'midline')->forceDelete();

        $gone = $this->pupil(['pupil_key' => 'gone']);
        $gone->delete();
        NutritionalStatus::withoutGlobalScope('survey_state')->withTrashed()->where('pupil_key', 'gone')->where('survey_state', 'endline')->forceDelete();

        $added = NutritionalStatus::syncRoster();

        $this->assertSame(['baseline', 'endline', 'midline'], $this->rows('partial')->keys()->sort()->values()->all());
        $this->assertSame('Partial', $this->rows('partial')['baseline']->first_name);
        $this->assertSame(2, $added, 'only the missing rows of the pupil that is not deleted');
        $this->assertSame(0, NutritionalStatus::withoutGlobalScope('survey_state')->where('pupil_key', 'gone')->count());
    }

    public function test_copy_button_fills_weight_and_height_for_the_same_pupils(): void
    {
        $birthday = Carbon::now()->subMonthsNoOverflow(122)->startOfDay();
        $months = ($birthday->diffInYears(now()) * 12) + $birthday->copy()->addYears($birthday->diffInYears(now()))->diffInMonths(now());
        DB::table('bmi_simplefied_version')->insert(['months' => $months, 'sex' => 'f', 'sd_minus_3' => 10, 'sd_minus_2' => 12, 'sd_plus_2' => 20, 'sd_plus_3' => 24]);
        DB::table('hfa_simplefied_version')->insert(['month' => $months, 'gender' => 'female', 'less_negative_3sd' => 120, 'to_less_negative_2sd' => 126, 'to_positive_2sd' => 150]);

        $ana = $this->pupil(['birthday' => $birthday->toDateString(), 'weight' => 30.0, 'height' => 140.0]);
        $this->pupil(['first_name' => 'Ben', 'full_name' => 'Ben', 'sex' => 'm', 'birthday' => $birthday->toDateString(), 'weight' => 33.0, 'height' => 138.0]);
        $this->assertSame(2, NutritionalStatus::count());

        SurveyState::set('midline');
        $this->assertSame(0, NutritionalStatus::whereNotNull('weight')->count());

        $button = new CopyMeasurements;
        $button->mount();
        $button->copy();

        // still the same two pupils, now with the baseline weight and height
        $this->assertSame(2, NutritionalStatus::count());
        $midline = NutritionalStatus::where('pupil_key', $ana->pupil_key)->first();
        $this->assertSame(30.0, $midline->weight);
        $this->assertSame(140.0, $midline->height);
        $this->assertSame('Normal', $midline->nutritional_status);
        $this->assertSame('Normal', $midline->height_for_age);

        // baseline is untouched and endline is still empty
        SurveyState::set('baseline');
        $this->assertSame(30.0, NutritionalStatus::where('pupil_key', $ana->pupil_key)->first()->weight);
        SurveyState::set('endline');
        $this->assertSame(0, NutritionalStatus::whereNotNull('weight')->count());
    }

    public function test_deleting_a_section_removes_its_pupils_from_every_state(): void
    {
        $ana = $this->pupil();
        $ben = $this->pupil(['first_name' => 'Ben', 'full_name' => 'Ben', 'section' => 'Rosal']);

        SurveyState::set('midline');
        $page = new TrackEnrollees;
        $page->selectedGrade = '5';
        $page->selectedSection = 'Sampaguita';
        $page->deleteSection();

        $this->assertSame(0, NutritionalStatus::withoutGlobalScope('survey_state')->where('pupil_key', $ana->pupil_key)->count());
        $this->assertSame(3, NutritionalStatus::withoutGlobalScope('survey_state')->where('pupil_key', $ben->pupil_key)->count());
    }
}
