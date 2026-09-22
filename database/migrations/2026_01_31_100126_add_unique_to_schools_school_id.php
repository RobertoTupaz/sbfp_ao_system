<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('schools')) {
            return;
        }

        // Fail early if duplicate school_id values exist; user must resolve them first.
        $duplicates = DB::select("SELECT school_id, COUNT(*) AS c FROM schools GROUP BY school_id HAVING c > 1");
        if (!empty($duplicates)) {
            $vals = array_map(function ($d) { return $d->school_id; }, $duplicates);
            $sample = implode(', ', array_slice($vals, 0, 10));
            throw new \Exception('Cannot add UNIQUE index: duplicate school_id values exist. Sample: ' . $sample);
        }

        // Drop any existing non-unique indexes on the column, then add a UNIQUE index.
        $indexes = DB::select("SHOW INDEX FROM schools WHERE Column_name = 'school_id'");
        foreach ($indexes as $index) {
            // Skip primary key index
            if (isset($index->Key_name) && $index->Key_name !== 'PRIMARY') {
                DB::statement(sprintf('DROP INDEX `%s` ON `schools`', $index->Key_name));
            }
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->unique('school_id', 'schools_school_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique('schools_school_id_unique');
            $table->index('school_id', 'schools_school_id_index');
        });
    }
};
