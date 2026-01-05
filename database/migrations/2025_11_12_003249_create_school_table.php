<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\District;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('district_id');
            $table->timestamps();

            $table->foreign('district_id')->references('id')->on('district')->onDelete('cascade');
        });

        $this->schools();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school');
    }

    public function schools() {
        $schools = [
            ['name' => 'KALASUNGAY CS', 'district_name' => 1],
            ['name' => 'PAT-PAT ES', 'district_name' => 1],
            ['name' => 'NEW ILOCOS ES', 'district_name' => 1],
            ['name' => 'DAMITAN ES', 'district_name' => 1],
            ['name' => 'CAPITAN ANGEL IS', 'district_name' => 1],
            ['name' => 'DALWANGAN ES', 'district_name' => 1],
            ['name' => 'PAT-PAT NHS', 'district_name' => 1],
            ['name' => 'KALASUNGAY NHS', 'district_name' => 1],
            ['name' => 'DALWANGAN NHS', 'district_name' => 1],

            ['name' => 'SUMPONG CS', 'district_name' => 2],
            ['name' => 'CAN-AYAN IS', 'district_name' => 2],
            ['name' => 'INCALBOG ES', 'district_name' => 2],
            ['name' => 'MANALOG ES', 'district_name' => 2],
            ['name' => 'KIBALABAG IS', 'district_name' => 2],
            ['name' => 'BAGANAO ES', 'district_name' => 2],
            ['name' => 'KILAP-AGAN ES', 'district_name' => 2],
            ['name' => 'TAGILANAO ES', 'district_name' => 2],
            ['name' => 'CANDIISAN IS', 'district_name' => 2],
            ['name' => 'TINTINAAN ES', 'district_name' => 2],

            ['name' => 'STA. ANA ES', 'district_name' => 3],
            ['name' => 'IMBAYAO ES', 'district_name' => 3],
            ['name' => 'BNHS', 'district_name' => 3],
            ['name' => 'IMBAYAO NHS', 'district_name' => 3],

            ['name' => 'MCCS', 'district_name' => 4],
            ['name' => 'BARANGAY 9 ES', 'district_name' => 4],
            ['name' => 'BCT ES', 'district_name' => 4],
            ['name' => 'CASISANG NHS', 'district_name' => 4],

            ['name' => 'CASISANG CIS', 'district_name' => 5],
            ['name' => 'BNHS SENIOR HS', 'district_name' => 5],
            ['name' => 'NATID-ASAN ES', 'district_name' => 5],
            ['name' => 'MABUHAY IS', 'district_name' => 5],
            ['name' => 'SAN JOSE ES', 'district_name' => 5],
            ['name' => 'MCNHS', 'district_name' => 5],
            ['name' => 'PANAMUCAN ES', 'district_name' => 5],
            ['name' => 'AVES', 'district_name' => 5],

            ['name' => 'AGLAYAN CS', 'district_name' => 6],
            ['name' => 'UPPER AGLAYAN ES', 'district_name' => 6],
            ['name' => 'MCSNHS', 'district_name' => 6],
            ['name' => 'CABANGAHAN ES', 'district_name' => 6],
            ['name' => 'LAGUITAS ES', 'district_name' => 6],
            ['name' => 'BALANGBANG ES', 'district_name' => 6],
            ['name' => 'MAPAYAG IS', 'district_name' => 6],
            ['name' => 'BENDOLAN ES', 'district_name' => 6],
            ['name' => 'MAGSAYSAY IS', 'district_name' => 6],
            ['name' => 'TAMOGAWE ES', 'district_name' => 6],

            ['name' => 'BANGCUD CS', 'district_name' => 7],
            ['name' => 'BANGCUD NHS', 'district_name' => 7],
            ['name' => 'CALAWAG ES', 'district_name' => 7],
            ['name' => 'DAPULAN IS', 'district_name' => 7],
            ['name' => 'MACOTE ES', 'district_name' => 7],
            ['name' => 'APO MACOTE NHS', 'district_name' => 7],
            ['name' => 'BINALBAGAN ES', 'district_name' => 7],
            ['name' => 'SIMAYA IS', 'district_name' => 7],
            ['name' => 'LUYUNGAN HS', 'district_name' => 7],
            ['name' => 'PADERNAL ES', 'district_name' => 7],

            ['name' => 'LINABO CS', 'district_name' => 8],
            ['name' => 'IAGES', 'district_name' => 8],
            ['name' => 'SAWAGA ES', 'district_name' => 8],
            ['name' => 'SAN MARTIN ES', 'district_name' => 8],
            ['name' => 'SMAINHS', 'district_name' => 8],
            ['name' => 'SAN ROQUE IS', 'district_name' => 8],
            ['name' => 'LALAWAN ES', 'district_name' => 8],
            ['name' => 'LALAWAN NHS', 'district_name' => 8],
            ['name' => 'PAIWAIG ES', 'district_name' => 8],

            ['name' => 'MANAGOK CS', 'district_name' => 9],
            ['name' => 'MANAGOK NHS', 'district_name' => 9],
            ['name' => 'MIGLAMIN ES', 'district_name' => 9],
            ['name' => 'MIGLAMIN NHS', 'district_name' => 9],
            ['name' => 'LUNOKAN IS', 'district_name' => 9],
            ['name' => 'MATANGPATANG ES', 'district_name' => 9],
            ['name' => 'LANGASIHAN ES', 'district_name' => 9],
            ['name' => 'MALIGAYA ES', 'district_name' => 9],
            ['name' => 'DUMAYAS ES', 'district_name' => 9],
            ['name' => 'BAGONG SILANG ES', 'district_name' => 9],

            ['name' => 'ZAMBOANGUITA CS', 'district_name' => 10],
            ['name' => 'BUSDI IS', 'district_name' => 10],
            ['name' => 'KULAMAN IS', 'district_name' => 10],
            ['name' => 'INDALASA ES', 'district_name' => 10],
            ['name' => 'KIBALABAG ES', 'district_name' => 10],
            ['name' => 'PIGHALUGAN ES', 'district_name' => 10],
            ['name' => 'CABURACANAN ES', 'district_name' => 10],
            ['name' => 'MAPULO ES', 'district_name' => 10],
            ['name' => 'TUBURAN IS', 'district_name' => 10],
            ['name' => 'SILAE ES', 'district_name' => 10],
            ['name' => 'SILAE NHS', 'district_name' => 10],
            ['name' => 'PIGPAMULAHAN IS', 'district_name' => 10],
            ['name' => 'ST. PETER ES', 'district_name' => 10],
            ['name' => 'ST. PETER NHS', 'district_name' => 10],
        ];

        foreach ($schools as $school) {
            $district = District::where('name', $school['district_name'])->first();
            School::create([
                'name' => $school['name'],
                'district_id' => $district->id,
            ]);
        }
    }
};
