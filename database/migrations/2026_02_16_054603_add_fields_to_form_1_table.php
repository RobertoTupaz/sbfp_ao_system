<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function _up(): void
    {
        Schema::table('form_1', function (Blueprint $table) {
            $table->foreignId('states')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function _down(): void
    {
        Schema::table('form_1', function (Blueprint $table) {
            $table->dropForeign(['states']);
        });
    }
};
