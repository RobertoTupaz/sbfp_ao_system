<?php

namespace Tests\Feature;

use App\Livewire\RetainDeleted\Base as RetainDeleted;
use App\Livewire\TrackEnrollees\Base as TrackEnrollees;
use App\Models\NutritionalStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RetainDeletedPupilsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleted_pupil_can_be_restored_with_nutritional_data_intact(): void
    {
        $user = User::factory()->create(['role' => 'ao']);
        $pupil = NutritionalStatus::create([
            'full_name' => 'Dela Cruz, Ana',
            'last_name' => 'Dela Cruz',
            'first_name' => 'Ana',
            'birthday' => '2017-01-15',
            'sex' => 'f',
            'weight' => 21.5,
            'height' => 120.4,
            'bmi' => 14.84,
            'nutritional_status' => 'normal',
            'height_for_age' => 'Normal',
            'grade' => '3',
            'section' => 'Mabini',
        ]);

        $this->actingAs($user);

        Livewire::test(TrackEnrollees::class)
            ->call('deletePupil', $pupil->id);

        $this->assertSoftDeleted('nutritional_statuses', [
            'id' => $pupil->id,
            'deleted_by' => $user->id,
        ]);

        Livewire::test(RetainDeleted::class)
            ->assertSee('Dela Cruz, Ana')
            ->call('restorePupil', $pupil->id);

        $restoredPupil = NutritionalStatus::findOrFail($pupil->id);

        $this->assertSame(21.5, $restoredPupil->weight);
        $this->assertSame(120.4, $restoredPupil->height);
        $this->assertSame('normal', $restoredPupil->nutritional_status);
        $this->assertSame('Normal', $restoredPupil->height_for_age);
        $this->assertNull($restoredPupil->deleted_at);
        $this->assertNull($restoredPupil->deleted_by);
    }

    public function test_retain_deleted_page_requires_authentication(): void
    {
        $this->get(route('retain_deleted'))
            ->assertRedirect('/login');
    }
}
