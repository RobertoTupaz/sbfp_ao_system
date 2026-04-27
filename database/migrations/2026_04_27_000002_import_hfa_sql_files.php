<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $base = base_path('public/sql');
        $files = [
            'hfa_measurements.sql',
            'hfa_simplefied_version.sql',
        ];

        foreach ($files as $file) {
            $path = $base . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($path)) {
                throw new \Exception("SQL file not found: {$path}");
            }

            $sql = file_get_contents($path);
            if ($sql === false) {
                throw new \Exception("Failed reading SQL file: {$path}");
            }

            DB::unprepared($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hfa_measurements');
        Schema::dropIfExists('hfa_simplefied_version');
    }
};
