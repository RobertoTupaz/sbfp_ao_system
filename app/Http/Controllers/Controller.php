<?php

namespace App\Http\Controllers;

use App\Models\HfaSimplifiedVersion;
use App\Models\BmiVersionSimplefied;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Database\Seeder;
use App\Models\NutritionalStatus;
use Faker\Factory as Faker;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function getHFA(Request $request)
    {
        $request->validate([
            'age_months' => 'required|numeric|min:60|max:228',
            'height_cm'  => 'required|numeric|min:30|max:220',
            'gender'     => 'required|in:male,female',
        ]);

        $ageMonths = $request->age_months;
        $height    = $request->height_cm;
        $gender    = $request->gender;

        $hfa = HfaSimplifiedVersion::where('month', $ageMonths)
            ->where('gender', $gender)
            ->first();

        if (!$hfa) {
            return response()->json([
                'error' => 'WHO reference not found for given age and gender.'
            ], 404);
        }

        $status = "";

        if ($height < $hfa->less_negative_3sd) {
            $status = 'Severely Stunted';
        } elseif ($height <= $hfa->to_less_negative_2sd) {
            $status = 'Stunted';
        } elseif ($height <= $hfa->to_positive_2sd) {
            $status = 'Normal';
        } else {
            $status = 'Tall';
        }

        return response()->json([
            'age_months' => $ageMonths,
            'gender'     => $gender,
            'height_cm'  => $height,
            'status'     => $status,
        ]);
    }

    public function getBMI(Request $request)
    {
        $request->validate([
            'age_months' => 'required|numeric|min:60|max:228',
            'bmi'        => 'required|numeric|min:2|max:80',
            'gender'     => 'required|in:male,female',
        ]);

        $ageMonths = $request->age_months;
        $bmi       = $request->bmi;
        $gender    = $request->gender;

        $sex = $gender === 'male' ? 'm' : 'f';

        $ref = BmiVersionSimplefied::where('months', $ageMonths)
            ->where('sex', $sex)
            ->first();

        if (!$ref) {
            return response()->json([
                'error' => 'WHO BMI-for-age reference not found for given age and gender.'
            ], 404);
        }

        $status = '';

        if ($bmi < $ref->sd_minus_3) {
            $status = 'Severely Wasted';
        } elseif ($bmi < $ref->sd_minus_2) {
            $status = 'Wasted';
        } elseif ($bmi <= $ref->sd_plus_2) {
            $status = 'Normal';
        } elseif ($bmi <= $ref->sd_plus_3) {
            $status = 'Overweight';
        } else {
            $status = 'Obese';
        }

        return response()->json([
            'age_months' => $ageMonths,
            'gender'     => $gender,
            'bmi'        => $bmi,
            'status'     => $status,
        ]);
    }

    public function nsSeeder()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 3000; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;
            $fullName = $firstName . ' ' . $lastName;
            NutritionalStatus::create([
                'full_name' => $fullName,
                'last_name' => $lastName,
                'first_name' => $firstName,
                'suffix_name' => $faker->optional()->suffix,
                'birthday' => $faker->dateTimeBetween('-12 years', '-5 years')->format('Y-m-d'),
                'sex' => $faker->randomElement(['M', 'F']),
                'weight' => $faker->randomFloat(2, 12, 60),
                'height' => $faker->numberBetween(90, 150),
                'age_years' => $faker->numberBetween(5, 12),
                'age_months' => $faker->numberBetween(0, 11),
                'bmi' => $faker->randomFloat(2, 12, 25),
                'nutritional_status' => $faker->randomElement(['severely wasted', 'wasted', 'normal', 'overweight', 'obese']),
                'height_for_age' => $faker->randomElement(['severely stunted', 'stunted', 'normal', 'tall']),
                'date_of_weighing' => Carbon::today()->toDateString(),
                'grade' => $faker->randomElement(array_merge(range(1, 12), ['k', 'non_graded'])),
                'section' => $faker->randomElement(['A', 'B', 'C', 'D']),
                '_4ps' => $faker->randomElement([0, 1]),
                'ip' => $faker->randomElement([0, 1]),
                'pardo' => $faker->word,
                'dewormed' => $faker->randomElement([0, 1]),
                'parent_consent_milk' => $faker->randomElement([0, 1]),
                'sbfp_previous_beneficiary' => $faker->randomElement([0, 1]),
            ]);
        }

        return "Seeded Successfully";
    }

}
