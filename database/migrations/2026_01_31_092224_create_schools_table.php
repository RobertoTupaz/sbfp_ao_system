<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('school_id')->index();
            $table->string('school_name');
            $table->timestamps();
        });

        // moved to add fields migration
        // $this->createSchools();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }

    /**
     * Read the JSON file at `public/data/schools.json` and log each school entry.
     */
    public function createSchools(): void
    {
        $schools = [
            ['id' => 126557, 'school' => 'Aglayan Central School'],
            ['id' => 126574, 'school' => 'Airport Village Elementary School'],
            ['id' => 325505, 'school' => 'Apo Macote National High School'],
            ['id' => 408493, 'school' => 'Apu Palamguwan Cultural Education Center, Inc.'],
            ['id' => 126575, 'school' => 'Baganao Elementary School'],
            ['id' => 126534, 'school' => 'Bagong Silang Elementary School'],
            ['id' => 126535, 'school' => 'Balangbang Elementary School'],
            ['id' => 126558, 'school' => 'Bangcud Central School'],
            ['id' => 303946, 'school' => 'Bangcud National High School'],
            ['id' => 199510, 'school' => 'Barangay 9 Elementary School'],
            ['id' => 126576, 'school' => 'BCT Elementary School'],
            ['id' => 126559, 'school' => 'Bendolan Elementary School'],
            ['id' => 404995, 'school' => 'Bethel Baptist Christian Academy'],
            ['id' => 409078, 'school' => 'Bible Baptist Malaybalay Christian Academy, Inc.'],
            ['id' => 126560, 'school' => 'Binalbagan Elementary School'],
            ['id' => 400609, 'school' => 'Brightspark Christian Academy'],
            ['id' => 400625, 'school' => 'BUGEMCO Learning Center'],
            ['id' => 303950, 'school' => 'Bukidnon National High School'],
            ['id' => 341061, 'school' => 'Bukidnon National High School Senior High School Campus'],
            ['id' => 600130, 'school' => 'Bukidnon State University'],
            ['id' => 500409, 'school' => 'Busdi Integrated School'],
            ['id' => 126561, 'school' => 'Cabangahan Elementary School'],
            ['id' => 126537, 'school' => 'Caburacanan Elementary School'],
            ['id' => 126562, 'school' => 'Calawag Elementary School'],
            ['id' => 500245, 'school' => 'Can-ayan Integrated School'],
            ['id' => 501195, 'school' => 'Candiisan Integrated School'],
            ['id' => 501189, 'school' => 'Capitan Angel Integrated School'],
            ['id' => 501580, 'school' => 'Casisang Central Integrated School'],
            ['id' => 404997, 'school' => 'Casisang International Christian School'],
            ['id' => 314914, 'school' => 'Casisang National High School'],
            ['id' => 126580, 'school' => 'Dalwangan Elementary School'],
            ['id' => 325504, 'school' => 'Dalwangan National High School'],
            ['id' => 103610, 'school' => 'Damitan Elementary School'],
            ['id' => 502945, 'school' => 'Dapulan Integrated School'],
            ['id' => 126539, 'school' => 'Dumayas Elementary School'],
            ['id' => 407945, 'school' => 'El Gibbor Faithhouse Academy Incorporated'],
            ['id' => 404998, 'school' => 'Heights Kinderland Inc.'],
            ['id' => 126581, 'school' => 'Imbayao Elementary School'],
            ['id' => 325503, 'school' => 'Imbayao National High School'],
            ['id' => 103677, 'school' => 'Incalbog Elementary School'],
            ['id' => 126540, 'school' => 'Indalasa Elementary School'],
            ['id' => 126568, 'school' => 'Isabela Ayala Gonzales Elementary School'],
            ['id' => 126582, 'school' => 'Kalasungay Central School'],
            ['id' => 314915, 'school' => 'Kalasungay National High School'],
            ['id' => 199518, 'school' => 'Kibalabag Elementary School'],
            ['id' => 501188, 'school' => 'Kibalabag Integrated School'],
            ['id' => 501579, 'school' => 'Kilap-agan Integrated School'],
            ['id' => 483543, 'school' => 'Kitanglad View Adventist Elementary School'],
            ['id' => 503036, 'school' => 'Kulaman Integrated School'],
            ['id' => 126564, 'school' => 'Laguitas Elementary School'],
            ['id' => 126542, 'school' => 'Lalawan Elementary School'],
            ['id' => 325501, 'school' => 'Lalawan National High School'],
            ['id' => 405000, 'school' => 'Lalawan SDA Elementary School'],
            ['id' => 126543, 'school' => 'Langasihan Elementary School'],
            ['id' => 126544, 'school' => 'Linabo Central School'],
            ['id' => 459520, 'school' => 'Little Gems Learning Center, Inc.'],
            ['id' => 410928, 'school' => 'Little Orchard School'],
            ['id' => 502744, 'school' => 'Lunokan Integrated School'],
            ['id' => 300507, 'school' => 'Luyungan High School'],
            ['id' => 501852, 'school' => 'Mabuhay Integrated School'],
            ['id' => 126566, 'school' => 'Macote Elementary School'],
            ['id' => 501190, 'school' => 'Magsaysay Integrated School'],
            ['id' => 405001, 'school' => 'Malaybalay City Adventist Elementary School, Inc.'],
            ['id' => 126586, 'school' => 'Malaybalay City Central School'],
            ['id' => 314916, 'school' => 'Malaybalay City National High School'],
            ['id' => 314904, 'school' => 'Malaybalay City National Science High School'],
            ['id' => 502800, 'school' => 'Maligaya Integrated School'],
            ['id' => 126547, 'school' => 'Managok Central School'],
            ['id' => 303973, 'school' => 'Managok National High School'],
            ['id' => 501854, 'school' => 'Manalog Integrated School'],
            ['id' => 501853, 'school' => 'Mapayag Integrated School'],
            ['id' => 126548, 'school' => 'Mapulo Elementary School'],
            ['id' => 459522, 'school' => 'Marywoods Academy Inc.'],
            ['id' => 126549, 'school' => 'Matangpatang Elementary School'],
            ['id' => 126550, 'school' => 'Miglamin Elementary School'],
            ['id' => 314920, 'school' => 'Miglamin National High School'],
            ['id' => 405003, 'school' => 'Mindanao Arts & Technological Institute, Inc.'],
            ['id' => 126588, 'school' => 'Natid-asan Elementary School'],
            ['id' => 126589, 'school' => 'New Ilocos Elementary School'],
            ['id' => 126570, 'school' => 'Padernal Elementary School'],
            ['id' => 199511, 'school' => 'Paiwaig Elementary School'],
            ['id' => 126551, 'school' => 'Panamucan Elementary School'],
            ['id' => 126590, 'school' => 'Patpat Elementary School'],
            ['id' => 306338, 'school' => 'Patpat National High School'],
            ['id' => 103629, 'school' => 'Pighalugan Elementary School'],
            ['id' => 502740, 'school' => 'Pigpamulahan Integrated School'],
            ['id' => 405004, 'school' => 'Saint Isidore High School'],
            ['id' => 405006, 'school' => 'San Isidro College'],
            ['id' => 126591, 'school' => 'San Jose Elementary School'],
            ['id' => 303982, 'school' => 'San Martin Agro-Industrial National High School'],
            ['id' => 126571, 'school' => 'San Martin–Sinanglanan Elementary School'],
            ['id' => 502738, 'school' => 'San Roque Integrated School'],
            ['id' => 126552, 'school' => 'Sawaga Elementary School'],
            ['id' => 126553, 'school' => 'Silae Elementary School'],
            ['id' => 303984, 'school' => 'Silae National High School'],
            ['id' => 501582, 'school' => 'Simaya Integrated School'],
            ['id' => 405008, 'school' => 'St. Isidore Academy of Bukidnon, Inc. Sinanglanan'],
            ['id' => 459521, 'school' => 'St. John\'s School of Malaybalay City, Inc.'],
            ['id' => 405009, 'school' => 'St. Michael High School Inc.'],
            ['id' => 126554, 'school' => 'St. Peter Elementary School'],
            ['id' => 314905, 'school' => 'St. Peter National High School'],
            ['id' => 126592, 'school' => 'Sta. Ana Elementary School'],
            ['id' => 403708, 'school' => 'STI Malaybalay'],
            ['id' => 126593, 'school' => 'Sumpong Central School'],
            ['id' => 405010, 'school' => 'Sunbeam Christian Academy of Bangcud, Inc.'],
            ['id' => 126594, 'school' => 'Tag-ilanao Elementary School'],
            ['id' => 137297, 'school' => 'Tamogawe Elementary School'],
            ['id' => 126595, 'school' => 'Tintinaan Elementary School'],
            ['id' => 501581, 'school' => 'Tuburan Integrated School'],
            ['id' => 126556, 'school' => 'Zamboanguita Central School'],
        ];

        foreach ($schools as $school) {
            \App\Models\AllSchool::create([
                'school_id' => $school['id'],
                'school_name' => $school['school'],
            ]);
        }
    }
};