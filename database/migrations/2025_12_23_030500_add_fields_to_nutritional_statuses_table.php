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
        Schema::table('nutritional_statuses', function (Blueprint $table) {
            $table->string('full_name')->after('id');
            $table->date('birthday')->nullable()->after('full_name');
            $table->string('sex')->nullable()->after('birthday');
            $table->decimal('weight', 6, 2)->nullable()->after('sex');
            $table->decimal('height', 6, 2)->nullable()->after('weight');
            $table->unsignedTinyInteger('age_years')->nullable()->after('height');
            $table->unsignedTinyInteger('age_months')->nullable()->after('age_years');
            $table->decimal('bmi', 5, 2)->nullable()->after('age_months');
            $table->string('nutritional_status')->nullable()->after('bmi');
            $table->string('height_for_age')->nullable()->after('nutritional_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritional_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'birthday',
                'sex',
                'weight',
                'height',
                'age_years',
                'age_months',
                'bmi',
                'nutritional_status',
                'height_for_age',
            ]);
        });
    }
};
