<?php

use App\Models\PrimarySecondaryBeneficiaries;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('primary_secondary_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('all_kinder')->default(true);
            $table->boolean('all_grade_1')->default(false);
            $table->boolean('all_grade_2')->default(false);
            $table->boolean('all_grade_3')->default(false);
            $table->boolean('severely_wasted')->default(true);
            $table->boolean('wasted')->default(true);
            $table->boolean('normal_weight')->default(false);
            $table->boolean('overweight_obese')->default(false);
            $table->boolean('severely_stunted')->default(false);
            $table->boolean('stunted')->default(false);
            $table->boolean('normal_height')->default(false);
            $table->boolean('tall')->default(false);
            $table->boolean('_4ps')->default(false);
            $table->boolean('ip')->default(false);
            $table->boolean('pardo')->default(false);
            $table->timestamps();
        });

        $this->createPrimarySecondaryBeneficiaries();
    }

    public function createPrimarySecondaryBeneficiaries()
    {
        $now = now();

        DB::table('primary_secondary_beneficiaries')->insert([
            [
                'name' => 'Primary',
                'all_kinder' => 1,
                'all_grade_1' => 0,
                'all_grade_2' => 0,
                'all_grade_3' => 0,
                'severely_wasted' => 1,
                'wasted' => 1,
                'normal_weight' => 0,
                'overweight_obese' => 0,
                'severely_stunted' => 0,
                'stunted' => 0,
                'normal_height' => 0,
                'tall' => 0,
                '_4ps' => 0,
                'ip' => 0,
                'pardo' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Secondary',
                'all_kinder' => 0,
                'all_grade_1' => 0,
                'all_grade_2' => 0,
                'all_grade_3' => 0,
                'severely_wasted' => 0,
                'wasted' => 0,
                'normal_weight' => 1,
                'overweight_obese' => 0,
                'severely_stunted' => 1,
                'stunted' => 1,
                'normal_height' => 0,
                'tall' => 0,
                '_4ps' => 1,
                'ip' => 1,
                'pardo' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primary_secondary_beneficiaries');
    }
};
