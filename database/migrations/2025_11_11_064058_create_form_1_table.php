<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     */
    public function up(): void
    {
        Schema::create('form_1', function (Blueprint $table) {
            $table->id();
            $table->string('school_year')->nullable();
            $table->string('name');
            $table->string('sex');
            $table->string('grade')->nullable();
            $table->string('section')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_weighing_or_measuring')->nullable();
            $table->string('age_in_years')->nullable();
            $table->string('age_in_months')->nullable();
            $table->integer('weight')->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->string('bmi_for_6_years_and_above')->nullable();
            $table->string('bmi_a');
            $table->string('hfa');
            $table->boolean('in_4ps')->nullable();
            $table->boolean('ip')->nullable();
            $table->boolean('pardo')->nullable();
            $table->boolean('dewormed')->nullable();
            $table->boolean('parent_consent_milk')->nullable();
            $table->boolean('beneficiary_of_sbfp_in_previous_year')->nullable();
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
