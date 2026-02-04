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
        Schema::table('schools', function (Blueprint $table) {
            $table->integer('district')->nullable()->after('school_name');
        });

        $this->createSchools();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            //
        });
    }


    public function createSchools(): void
    {
        $schools = [
            ['id' => 1, 'school_id' => '126557', 'school' => 'Aglayan Central School', 'district' => 6],
            ['id' => 2, 'school_id' => '126574', 'school' => 'Airport Village Elementary School', 'district' => 5],
            ['id' => 3, 'school_id' => '325505', 'school' => 'Apo Macote National High School', 'district' => 7],
            ['id' => 4, 'school_id' => '408493', 'school' => 'Apu Palamguwan Cultural Education Center, Inc.', 'district' => null],
            ['id' => 5, 'school_id' => '126575', 'school' => 'Baganao Elementary School', 'district' => 2],
            ['id' => 6, 'school_id' => '126534', 'school' => 'Bagong Silang Elementary School', 'district' => 9],
            ['id' => 7, 'school_id' => '126535', 'school' => 'Balangbang Elementary School', 'district' => 6],
            ['id' => 8, 'school_id' => '126558', 'school' => 'Bangcud Central School', 'district' => 7],
            ['id' => 9, 'school_id' => '303946', 'school' => 'Bangcud National High School', 'district' => 7],
            ['id' => 10, 'school_id' => '199510', 'school' => 'Barangay 9 Elementary School', 'district' => 4],
            ['id' => 11, 'school_id' => '126576', 'school' => 'BCT Elementary School', 'district' => 4],
            ['id' => 12, 'school_id' => '126559', 'school' => 'Bendolan Elementary School', 'district' => 6],
            ['id' => 13, 'school_id' => '404995', 'school' => 'Bethel Baptist Christian Academy', 'district' => null],
            ['id' => 14, 'school_id' => '409078', 'school' => 'Bible Baptist Malaybalay Christian Academy, Inc.', 'district' => null],
            ['id' => 15, 'school_id' => '126560', 'school' => 'Binalbagan Elementary School', 'district' => 7],
            ['id' => 16, 'school_id' => '400609', 'school' => 'Brightspark Christian Academy', 'district' => null],
            ['id' => 17, 'school_id' => '400625', 'school' => 'BUGEMCO Learning Center', 'district' => null],
            ['id' => 18, 'school_id' => '303950', 'school' => 'Bukidnon National High School', 'district' => 3],
            ['id' => 19, 'school_id' => '341061', 'school' => 'Bukidnon National High School Senior High School Campus', 'district' => 5],
            ['id' => 20, 'school_id' => '600130', 'school' => 'Bukidnon State University', 'district' => null],
            ['id' => 21, 'school_id' => '500409', 'school' => 'Busdi Integrated School', 'district' => 10],
            ['id' => 22, 'school_id' => '126561', 'school' => 'Cabangahan Elementary School', 'district' => 6],
            ['id' => 23, 'school_id' => '126537', 'school' => 'Caburacanan Elementary School', 'district' => 10],
            ['id' => 24, 'school_id' => '126562', 'school' => 'Calawag Elementary School', 'district' => 7],
            ['id' => 25, 'school_id' => '500245', 'school' => 'Can-ayan Integrated School', 'district' => 2],
            ['id' => 26, 'school_id' => '501195', 'school' => 'Candiisan Integrated School', 'district' => 2],
            ['id' => 27, 'school_id' => '501189', 'school' => 'Capitan Angel Integrated School', 'district' => 1],
            ['id' => 28, 'school_id' => '501580', 'school' => 'Casisang Central Integrated School', 'district' => 4],
            ['id' => 29, 'school_id' => '404997', 'school' => 'Casisang International Christian School', 'district' => null],
            ['id' => 30, 'school_id' => '314914', 'school' => 'Casisang National High School', 'district' => 4],
            ['id' => 31, 'school_id' => '126580', 'school' => 'Dalwangan Elementary School', 'district' => 1],
            ['id' => 32, 'school_id' => '325504', 'school' => 'Dalwangan National High School', 'district' => 1],
            ['id' => 33, 'school_id' => '103610', 'school' => 'Damitan Elementary School', 'district' => 1],
            ['id' => 34, 'school_id' => '502945', 'school' => 'Dapulan Integrated School', 'district' => 7],
            ['id' => 35, 'school_id' => '126539', 'school' => 'Dumayas Elementary School', 'district' => 9],
            ['id' => 36, 'school_id' => '407945', 'school' => 'El Gibbor Faithhouse Academy Incorporated', 'district' => null],
            ['id' => 37, 'school_id' => '404998', 'school' => 'Heights Kinderland Inc.', 'district' => null],
            ['id' => 38, 'school_id' => '126581', 'school' => 'Imbayao Elementary School', 'district' => 3],
            ['id' => 39, 'school_id' => '325503', 'school' => 'Imbayao National High School', 'district' => 3],
            ['id' => 40, 'school_id' => '103677', 'school' => 'Incalbog Elementary School', 'district' => 2],
            ['id' => 41, 'school_id' => '126540', 'school' => 'Indalasa Elementary School', 'district' => 10],
            ['id' => 42, 'school_id' => '126568', 'school' => 'Isabela Ayala Gonzales Elementary School', 'district' => 8],
            ['id' => 43, 'school_id' => '126582', 'school' => 'Kalasungay Central School', 'district' => 1],
            ['id' => 44, 'school_id' => '314915', 'school' => 'Kalasungay National High School', 'district' => 1],
            ['id' => 45, 'school_id' => '199518', 'school' => 'Kibalabag Elementary School', 'district' => 10],
            ['id' => 46, 'school_id' => '501188', 'school' => 'Kibalabag Integrated School', 'district' => 2],
            ['id' => 47, 'school_id' => '501579', 'school' => 'Kilap-agan Integrated School', 'district' => 2],
            ['id' => 48, 'school_id' => '483543', 'school' => 'Kitanglad View Adventist Elementary School', 'district' => null],
            ['id' => 49, 'school_id' => '503036', 'school' => 'Kulaman Integrated School', 'district' => 10],
            ['id' => 50, 'school_id' => '126564', 'school' => 'Laguitas Elementary School', 'district' => 6],
            ['id' => 51, 'school_id' => '126542', 'school' => 'Lalawan Elementary School', 'district' => 8],
            ['id' => 52, 'school_id' => '325501', 'school' => 'Lalawan National High School', 'district' => null],
            ['id' => 53, 'school_id' => '405000', 'school' => 'Lalawan SDA Elementary School', 'district' => null],
            ['id' => 54, 'school_id' => '126543', 'school' => 'Langasihan Elementary School', 'district' => 9],
            ['id' => 55, 'school_id' => '126544', 'school' => 'Linabo Central School', 'district' => 8],
            ['id' => 56, 'school_id' => '459520', 'school' => 'Little Gems Learning Center, Inc.', 'district' => null],
            ['id' => 57, 'school_id' => '410928', 'school' => 'Little Orchard School', 'district' => null],
            ['id' => 58, 'school_id' => '502744', 'school' => 'Lunokan Integrated School', 'district' => 9],
            ['id' => 59, 'school_id' => '300507', 'school' => 'Luyungan High School', 'district' => 7],
            ['id' => 60, 'school_id' => '501852', 'school' => 'Mabuhay Integrated School', 'district' => 5],
            ['id' => 61, 'school_id' => '126566', 'school' => 'Macote Elementary School', 'district' => 7],
            ['id' => 62, 'school_id' => '501190', 'school' => 'Magsaysay Integrated School', 'district' => 6],
            ['id' => 63, 'school_id' => '405001', 'school' => 'Malaybalay City Adventist Elementary School, Inc.', 'district' => null],
            ['id' => 64, 'school_id' => '126586', 'school' => 'Malaybalay City Central School', 'district' => 4],
            ['id' => 65, 'school_id' => '314916', 'school' => 'Malaybalay City National High School', 'district' => 5],
            ['id' => 66, 'school_id' => '314904', 'school' => 'Malaybalay City National Science High School', 'district' => null],
            ['id' => 67, 'school_id' => '502800', 'school' => 'Maligaya Integrated School', 'district' => 9],
            ['id' => 68, 'school_id' => '126547', 'school' => 'Managok Central School', 'district' => 9],
            ['id' => 69, 'school_id' => '303973', 'school' => 'Managok National High School', 'district' => 9],
            ['id' => 70, 'school_id' => '501854', 'school' => 'Manalog Integrated School', 'district' => 2],
            ['id' => 71, 'school_id' => '501853', 'school' => 'Mapayag Integrated School', 'district' => 6],
            ['id' => 72, 'school_id' => '126548', 'school' => 'Mapulo Elementary School', 'district' => 10],
            ['id' => 73, 'school_id' => '459522', 'school' => 'Marywoods Academy Inc.', 'district' => null],
            ['id' => 74, 'school_id' => '126549', 'school' => 'Matangpatang Elementary School', 'district' => 9],
            ['id' => 75, 'school_id' => '126550', 'school' => 'Miglamin Elementary School', 'district' => 9],
            ['id' => 76, 'school_id' => '314920', 'school' => 'Miglamin National High School', 'district' => 9],
            ['id' => 77, 'school_id' => '405003', 'school' => 'Mindanao Arts & Technological Institute, Inc.', 'district' => null],
            ['id' => 78, 'school_id' => '126588', 'school' => 'Natid-asan Elementary School', 'district' => 5],
            ['id' => 79, 'school_id' => '126589', 'school' => 'New Ilocos Elementary School', 'district' => 1],
            ['id' => 80, 'school_id' => '126570', 'school' => 'Padernal Elementary School', 'district' => 7],
            ['id' => 81, 'school_id' => '199511', 'school' => 'Paiwaig Elementary School', 'district' => 8],
            ['id' => 82, 'school_id' => '126551', 'school' => 'Panamucan Elementary School', 'district' => 5],
            ['id' => 83, 'school_id' => '126590', 'school' => 'Patpat Elementary School', 'district' => 1],
            ['id' => 84, 'school_id' => '306338', 'school' => 'Patpat National High School', 'district' => 1],
            ['id' => 85, 'school_id' => '103629', 'school' => 'Pighalugan Elementary School', 'district' => 10],
            ['id' => 86, 'school_id' => '502740', 'school' => 'Pigpamulahan Integrated School', 'district' => 10],
            ['id' => 87, 'school_id' => '405004', 'school' => 'Saint Isidore High School', 'district' => null],
            ['id' => 88, 'school_id' => '405006', 'school' => 'San Isidro College', 'district' => null],
            ['id' => 89, 'school_id' => '126591', 'school' => 'San Jose Elementary School', 'district' => 5],
            ['id' => 90, 'school_id' => '303982', 'school' => 'San Martin Agro-Industrial National High School', 'district' => 8],
            ['id' => 91, 'school_id' => '126571', 'school' => 'San Martin–Sinanglanan Elementary School', 'district' => 8],
            ['id' => 92, 'school_id' => '502738', 'school' => 'San Roque Integrated School', 'district' => 8],
            ['id' => 93, 'school_id' => '126552', 'school' => 'Sawaga Elementary School', 'district' => 8],
            ['id' => 94, 'school_id' => '126553', 'school' => 'Silae Elementary School', 'district' => 10],
            ['id' => 95, 'school_id' => '303984', 'school' => 'Silae National High School', 'district' => 10],
            ['id' => 96, 'school_id' => '501582', 'school' => 'Simaya Integrated School', 'district' => 7],
            ['id' => 97, 'school_id' => '405008', 'school' => 'St. Isidore Academy of Bukidnon, Inc. Sinanglanan', 'district' => null],
            ['id' => 98, 'school_id' => '459521', 'school' => 'St. John\'s School of Malaybalay City, Inc.', 'district' => null],
            ['id' => 99, 'school_id' => '405009', 'school' => 'St. Michael High School Inc.', 'district' => null],
            ['id' => 100, 'school_id' => '126554', 'school' => 'St. Peter Elementary School', 'district' => 10],
            ['id' => 101, 'school_id' => '314905', 'school' => 'St. Peter National High School', 'district' => 10],
            ['id' => 102, 'school_id' => '126592', 'school' => 'Sta. Ana Elementary School', 'district' => 3],
            ['id' => 103, 'school_id' => '403708', 'school' => 'STI Malaybalay', 'district' => null],
            ['id' => 104, 'school_id' => '126593', 'school' => 'Sumpong Central School', 'district' => 2],
            ['id' => 105, 'school_id' => '405010', 'school' => 'Sunbeam Christian Academy of Bangcud, Inc.', 'district' => null],
            ['id' => 106, 'school_id' => '126594', 'school' => 'Tag-ilanao Elementary School', 'district' => 2],
            ['id' => 107, 'school_id' => '137297', 'school' => 'Tamogawe Elementary School', 'district' => 6],
            ['id' => 108, 'school_id' => '126595', 'school' => 'Tintinaan Elementary School', 'district' => 2],
            ['id' => 109, 'school_id' => '501581', 'school' => 'Tuburan Integrated School', 'district' => 10],
            ['id' => 110, 'school_id' => '126556', 'school' => 'Zamboanguita Central School', 'district' => 10],
        ];


        foreach ($schools as $school) {
            \App\Models\AllSchool::create([
                'school_id' => $school['school_id'],
                'school_name' => $school['school'],
                'district' => $school['district'],
            ]);
        }
    }
};
