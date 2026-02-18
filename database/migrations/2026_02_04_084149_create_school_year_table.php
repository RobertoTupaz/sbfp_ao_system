<?php

use App\Models\SchoolYear;
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
        Schema::create('school_year', function (Blueprint $table) {
            $table->id();
            $table->string('school_year');
            $table->timestamps();
        });

        $this->createSchoolYearUser();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_year');
    }

    public function createSchoolYearUser(): void
    {
        for ($i = 2022; $i <= 2030; $i++) {
            SchoolYear::create([
                'school_year' => $i,
            ]);
        }
    }
};
