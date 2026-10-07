<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nutritional_statuses', function (Blueprint $table) {
            $table->string('survey_state', 20)->default('baseline')->after('id')->index();
            $table->char('pupil_key', 36)->nullable()->after('survey_state')->index();
        });

        // Existing records become the baseline; give each pupil a stable key
        DB::table('nutritional_statuses')->whereNull('pupil_key')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('nutritional_statuses')->where('id', $row->id)->update(['pupil_key' => (string) Str::uuid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('nutritional_statuses', function (Blueprint $table) {
            $table->dropColumn(['survey_state', 'pupil_key']);
        });
    }
};
