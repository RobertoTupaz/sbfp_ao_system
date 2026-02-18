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
        Schema::table('form_1', function (Blueprint $table) {
            if (!Schema::hasColumn('form_1', 'survey_state')) {
                $table->string('survey_state')->nullable()->after('school_year');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_1', function (Blueprint $table) {
            if (Schema::hasColumn('form_1', 'survey_state')) {
                $table->dropColumn('survey_state');
            }
        });
    }
};
