<?php

use App\Models\District;
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
        Schema::create('district', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        $this->districs();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('district');
    }

        public function districs() {
        $districts = [
            '1', '2', '3','4', '5', '6', '7', '8', '9', '10'
        ];

        foreach ($districts as $district) {
            District::create([
                'name' => $district,
            ]);
        }
    }
};
