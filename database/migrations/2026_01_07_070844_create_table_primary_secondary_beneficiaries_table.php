<?php

use App\Models\PrimarySecondaryBeneficiaries;
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
        PrimarySecondaryBeneficiaries::create([
            'name' => 'Primary',
            'all_kinder' => true,
            'all_grade_1' => false,
            'all_grade_2' => false,
            'all_grade_3' => false,
            'severely_wasted' => true,
            'wasted' => true,
            'normal_weight' => false,
            'overweight_obese' => false,
            'severely_stunted' => false,
            'stunted' => false,
            'normal_height' => false,
            'tall' => false,
            '_4ps' => false,
            'ip'=> false,
            'pardo'=> false,

        ]);

        PrimarySecondaryBeneficiaries::create([
            'name' => 'Secondary',
            'all_kinder' => false,
            'all_grade_1' => false,
            'all_grade_2' => false,
            'all_grade_3' => false,
            'severely_wasted' => false,
            'wasted' => false,
            'normal_weight' => true,
            'overweight_obese' => false,
            'severely_stunted' => true,
            'stunted' => true,
            'normal_height' => false,
            'tall' => false,
            '_4ps'=> true,
            'ip'=> true,
            'pardo'=> true,
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
