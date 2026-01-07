<?php

use App\Models\Beneficiaries;
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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->string('beneficiaries_count')->nullable();
            $table->timestamps();
        });

        $this->createBeneficiaries();
    }

    public function createBeneficiaries() {
        Beneficiaries::create([
            'beneficiaries_count' => '0',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
