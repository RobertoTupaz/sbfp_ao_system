<?php

use App\Models\NutritionalStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Baseline, midline and endline hold the same pupils. Pupils that only exist in some of the states
     * (added or seeded before the roster was shared) are added to the others, without measurements.
     */
    public function up(): void
    {
        NutritionalStatus::syncRoster();
    }

    public function down(): void
    {
        // the added rows are ordinary pupil rows; nothing to undo
    }
};
