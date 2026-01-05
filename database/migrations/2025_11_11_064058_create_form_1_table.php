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
        Schema::create('form_1', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sex');
            $table->string('grade_section');
            $table->date('date_of_birth');
            $table->date('date_of_weighing_or_measuring');
            $table->string('age_in_years_or_months');
            $table->integer('weight');
            $table->decimal('height');
            $table->string('bmi_for_6_years_and_above');
            $table->string('bmi_a');
            $table->string('bmi_b');
            $table->boolean('parents_consent_for_milk');
            $table->boolean('participation_in_4ps');
            $table->boolean('beneficiary_of_sbfp_in_previous_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_1');
    }
};
