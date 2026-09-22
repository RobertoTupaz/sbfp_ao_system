<?php

namespace Tests\Feature;

use App\Livewire\EditBeneficiaries\BeneficiariesList;
use App\Models\NutritionalStatus;
use App\Models\SwappedPupils;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class BeneficiarySwapTest extends TestCase
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

        Schema::create('swapped_pupils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_pupil_id');
            $table->foreignId('new_pupil_id');
            $table->string('reason')->nullable();
            $table->date('swap_date')->nullable();
            $table->timestamps();
            $table->unique('old_pupil_id');
        });
    }

    public function test_a_beneficiary_can_be_swapped_and_the_change_is_audited(): void
    {
        $from = $this->createPupil('Current Beneficiary', true);
        $to = $this->createPupil('Replacement Pupil', false);

        Livewire::test(BeneficiariesList::class)
            ->call('openSwapModal', $from->id)
            ->set('swapReason', 'Transferred')
            ->call('applySwap', $to->id)
            ->assertDispatched('swapped-success')
            ->assertSet('showSwapModal', false);

        $this->assertFalse($from->fresh()->isBeneficiary);
        $this->assertTrue($to->fresh()->isBeneficiary);
        $this->assertDatabaseHas('swapped_pupils', [
            'old_pupil_id' => $from->id,
            'new_pupil_id' => $to->id,
            'reason' => 'Transferred',
        ]);
    }

    public function test_a_swap_failure_is_shown_in_the_modal(): void
    {
        $from = $this->createPupil('Previously Replaced Beneficiary', true);
        $previousReplacement = $this->createPupil('Previous Replacement', false);
        $to = $this->createPupil('New Replacement', false);

        SwappedPupils::create([
            'old_pupil_id' => $from->id,
            'new_pupil_id' => $previousReplacement->id,
            'reason' => 'Previous swap',
        ]);

        Livewire::test(BeneficiariesList::class)
            ->call('openSwapModal', $from->id)
            ->call('applySwap', $to->id)
            ->assertDispatched('swapped-error')
            ->assertSet('swapError', 'Swap failed: This beneficiary has already been replaced before.')
            ->assertSet('showSwapModal', true);

        $this->assertTrue($from->fresh()->isBeneficiary);
        $this->assertFalse($to->fresh()->isBeneficiary);
    }

    private function createPupil(string $name, bool $isBeneficiary): NutritionalStatus
    {
        return NutritionalStatus::create([
            'full_name' => $name,
            'first_name' => $name,
            'last_name' => 'Test',
            'birthday' => '2017-01-15',
            'sex' => 'f',
            'grade' => '3',
            'section' => 'Mabini',
            'isBeneficiary' => $isBeneficiary,
        ]);
    }
}
