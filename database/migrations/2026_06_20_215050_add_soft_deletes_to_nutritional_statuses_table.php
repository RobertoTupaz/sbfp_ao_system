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
        if (! Schema::hasColumn('nutritional_statuses', 'deleted_at')) {
            Schema::table('nutritional_statuses', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('nutritional_statuses', 'deleted_by')) {
            Schema::table('nutritional_statuses', function (Blueprint $table) {
                $table->foreignId('deleted_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('nutritional_statuses', 'deleted_by')) {
            Schema::table('nutritional_statuses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('deleted_by');
            });
        }

        if (Schema::hasColumn('nutritional_statuses', 'deleted_at')) {
            Schema::table('nutritional_statuses', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
