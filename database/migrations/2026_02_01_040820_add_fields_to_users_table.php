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
        Schema::table('users', function (Blueprint $table) {
            $table->string('school_id')->nullable()->after('password');
            $table->string('role')->enum(['focal', 'ao', 'user', 'admin'])->default('user')->after('password');

            $table->foreign('school_id')
                ->references('school_id')
                ->on('schools')
                ->nullOnDelete();
        });

        $this->createUser();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('users');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
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
