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
    // public function up(): void
    // {
    //     Schema::table('users', function (Blueprint $table) {
    //         $table->string('school_id')->nullable()->after('password');
    //         $table->string('role')->enum(['focal', 'ao', 'user', 'admin'])->default('user')->after('password');

    //         $table->foreign('school_id')
    //             ->references('school_id')
    //             ->on('schools')
    //             ->nullOnDelete();
    //     });

    //     $this->createUser();
    // }

    public function up(): void
    {
        // Step 1: Add columns safely
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'school_id')) {
                $table->string('school_id')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password');
                // OR: $table->enum('role', ['focal','ao','user','admin'])->default('user');
            }
        });

        // Step 2: Add foreign key only if missing
        $fkExists = DB::select("
        SELECT CONSTRAINT_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = 'users'
        AND COLUMN_NAME = 'school_id'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");

        if (empty($fkExists)) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('school_id')
                    ->references('school_id')
                    ->on('schools')
                    ->nullOnDelete();
            });
        }

        // Step 3: Run your seeding logic
        $this->createUser();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('users');
        Schema::table('users', function (Blueprint $table) {
            // $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
            $table->dropColumn('role');
        });
    }

    protected function createUser()
    {
        \App\Models\User::create([
            'name' => 'SBFP Admin',
            'email' => env('ADMIN_LOGIN', 'admin@deped.gov.ph'),
            'password' => env('ADMIN_PASSWORD', 'admin@deped.gov.ph'),
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'SBFP Focal',
            'email' => env('FOCAL_LOGIN', 'focal@deped.gov.ph'),
            'password' => env('FOCAL_PASSWORD', 'focal@deped.gov.ph'),
            'role' => 'focal',
        ]);

        \App\Models\User::create([
            'name' => 'SBFP User',
            'email' => env('USER_LOGIN', 'user@deped.gov.ph'),
            'password' => env('USER_PASSWORD', 'user@deped.gov.ph'),
            'role' => 'user',
        ]);

        \App\Models\User::create([
            'name' => 'SBFP AO',
            'email' => env('AO_LOGIN', 'ao@deped.gov.ph'),
            'password' => env('AO_PASSWORD', 'ao@deped.gov.ph'),
            'role' => 'ao',
        ]);
    }
};
