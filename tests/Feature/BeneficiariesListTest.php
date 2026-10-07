<?php

namespace Tests\Feature;

use App\Livewire\EditBeneficiaries\BeneficiariesList;
use App\Models\NutritionalStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The beneficiaries list on Edit Beneficiaries is paginated and sorted Kinder to Grade 12.
 * Runs on its own in-memory SQLite database.
 */
class BeneficiariesListTest extends TestCase
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
            $table->date('birthday')->nullable();
            $table->string('sex')->nullable();
            $table->string('grade')->nullable();
            $table->string('section')->nullable();
            $table->boolean('isBeneficiary')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    private function pupil(string $name, string $grade, bool $beneficiary = true, ?string $section = 'A'): NutritionalStatus
    {
        return NutritionalStatus::create([
            'full_name' => $name,
            'first_name' => $name,
            'last_name' => $name,
            'birthday' => '2016-01-15',
            'sex' => 'f',
            'grade' => $grade,
            'section' => $section,
            'isBeneficiary' => $beneficiary,
        ]);
    }

    public function test_pupils_are_listed_from_kinder_to_grade_12_then_non_graded(): void
    {
        // created in a scrambled order; "10" must not sort before "2"
        foreach (['non_graded' => 'ZZ-NG', '12' => 'ZZ-12', '2' => 'ZZ-02', 'k' => 'ZZ-KK', '10' => 'ZZ-10', '1' => 'ZZ-01', '7' => 'ZZ-07'] as $grade => $name) {
            $this->pupil($name, $grade);
        }

        Livewire::test(BeneficiariesList::class)
            ->assertSeeInOrder(['ZZ-KK', 'ZZ-01', 'ZZ-02', 'ZZ-07', 'ZZ-10', 'ZZ-12', 'ZZ-NG']);
    }

    public function test_within_a_grade_pupils_are_sorted_by_section_then_name(): void
    {
        $this->pupil('ZZ-Zamora', '3', true, 'Rosal');
        $this->pupil('ZZ-Abad', '3', true, 'Sampaguita');
        $this->pupil('ZZ-Bautista', '3', true, 'Rosal');

        Livewire::test(BeneficiariesList::class)
            ->assertSeeInOrder(['ZZ-Bautista', 'ZZ-Zamora', 'ZZ-Abad']);
    }

    public function test_the_list_is_paginated(): void
    {
        foreach (range(1, 17) as $i) {
            $this->pupil(sprintf('Pupil %02d', $i), '1');
        }

        Livewire::test(BeneficiariesList::class)
            ->assertSee('Pupil 01')
            ->assertSee('Pupil 15')
            ->assertDontSee('Pupil 16')
            ->call('gotoPage', 2)
            ->assertSee('Pupil 16')
            ->assertSee('Pupil 17')
            ->assertDontSee('Pupil 01');
    }

    public function test_search_filters_the_list_and_returns_to_the_first_page(): void
    {
        foreach (range(1, 17) as $i) {
            $this->pupil(sprintf('Pupil %02d', $i), '1');
        }

        Livewire::test(BeneficiariesList::class)
            ->call('gotoPage', 2)
            ->set('search', 'Pupil 03')
            ->call('searchBeneficiaries')
            ->assertSee('Pupil 03')
            ->assertDontSee('Pupil 04')
            ->call('clearSearch')
            ->assertSee('Pupil 01');
    }

    public function test_only_beneficiaries_are_listed(): void
    {
        $this->pupil('Selected', '1', true);
        $this->pupil('Not selected', '1', false);

        Livewire::test(BeneficiariesList::class)
            ->assertSee('Selected')
            ->assertDontSee('Not selected');
    }

    public function test_it_shows_a_message_when_there_are_no_beneficiaries(): void
    {
        Livewire::test(BeneficiariesList::class)->assertSee('No beneficiaries found.');
    }
}
