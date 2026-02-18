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
            // store the external school identifier from the schools/all_schools table
            $table->string('school_id')->after('school_year')->nullable();
            $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_1', function (Blueprint $table) {
            // drop foreign key first, then the column
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
