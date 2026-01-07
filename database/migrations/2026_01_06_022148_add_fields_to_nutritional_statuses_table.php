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
            Schema::table('nutritional_statuses', function (Blueprint $table) {
                $table->string('grade')->nullable()->after('full_name');
                $table->string('section')->nullable()->after('grade');
                $table->string('_4ps')->nullable()->after('height_for_age');
                $table->string('ip')->nullable()->after('_4ps');
                $table->string('pardo')->nullable()->after('ip');
                $table->string('dewormed')->nullable()->after('pardo');
                $table->string('parent_consent_milk')->nullable()->after('dewormed');
                $table->string('sbfp_previous_beneficiary')->nullable()->after('parent_consent_milk');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritional_statuses', function (Blueprint $table) {
            $table->dropColumn([
                'sbfp_previous_beneficiary',
                'parent_consent_milk',
                'dewormed',
                'pardo',
                'ip',
                '_4ps',
                'section',
                'grade',
            ]);
        });
    }
};
