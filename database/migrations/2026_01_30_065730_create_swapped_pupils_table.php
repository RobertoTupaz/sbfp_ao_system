<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('swapped_pupils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_pupil_id')
                ->constrained('nutritional_statuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('new_pupil_id')
                ->constrained('nutritional_statuses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('reason')->nullable(); // dropout, transfer, deceased, etc
            $table->date('swap_date')->default(now());

            // $table->foreignId('swapped_by')->nullable()
            //     ->constrained('users');

            $table->timestamps();

            $table->unique('old_pupil_id'); // one-time replacement only
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swapped_pupils');
    }
};
