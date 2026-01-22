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
            $table->boolean('isBeneficiary')->default(false)->after('date_of_weighing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritional_statuses', function (Blueprint $table) {
            $table->dropColumn('isBeneficiary');
        });
    }
};
