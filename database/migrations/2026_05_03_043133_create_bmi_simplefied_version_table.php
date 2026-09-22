<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bmi_simplefied_version', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('year')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->integer('months')->nullable();
            $table->char('sex', 1)->nullable();

            $table->float('sd_minus_3', 6, 2)->nullable();
            $table->float('sd_minus_2', 6, 2)->nullable();
            $table->float('sd_minus_1', 6, 2)->nullable();
            $table->float('median', 6, 2)->nullable();
            $table->float('sd_plus_1', 6, 2)->nullable();
            $table->float('sd_plus_2', 6, 2)->nullable();
            $table->float('sd_plus_3', 6, 2)->nullable();

            $table->timestamps();
        });

        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bmi_simplefied_version');
    }


    public function uploadData() {
        $files = [
            public_path('data/boys_bmi_z_5_19.json'),
            public_path('data/girls_bmi_z_5_19.json'),
        ];

        foreach ($files as $path) {
            if (!File::exists($path)) {
                continue;
            }

            $json = json_decode(File::get($path), true);
            if (!is_array($json)) {
                continue;
            }

            $batch = [];
            foreach ($json as $row) {
                $year = null;
                $month = null;
                if (!empty($row['Year: Month'])) {
                    // expected format like "5: 1"
                    $parts = preg_split('/:\s*/', $row['Year: Month']);
                    if (count($parts) >= 2) {
                        $year = intval($parts[0]);
                        $month = intval($parts[1]);
                    }
                }

                $batch[] = [
                    'year' => $year ?: null,
                    'month' => $month ?: null,
                    'months' => isset($row['Months']) ? intval($row['Months']) : null,
                    'sex' => isset($row['sex']) ? strtolower(substr($row['sex'], 0, 1)) : null,
                    'sd_minus_3' => isset($row['-3 SD']) ? (float)$row['-3 SD'] : null,
                    'sd_minus_2' => isset($row['-2 SD']) ? (float)$row['-2 SD'] : null,
                    'sd_minus_1' => isset($row['-1 SD']) ? (float)$row['-1 SD'] : null,
                    'median' => isset($row['Median']) ? (float)$row['Median'] : null,
                    'sd_plus_1' => isset($row['1 SD']) ? (float)$row['1 SD'] : null,
                    'sd_plus_2' => isset($row['2 SD']) ? (float)$row['2 SD'] : null,
                    'sd_plus_3' => isset($row['3 SD']) ? (float)$row['3 SD'] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= 500) {
                    DB::table('bmi_simplefied_version')->insert($batch);
                    $batch = [];
                }
            }

            if (count($batch)) {
                DB::table('bmi_simplefied_version')->insert($batch);
            }
        }
    }
};
