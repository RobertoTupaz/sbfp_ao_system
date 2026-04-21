<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NutritionalStatus;
use Faker\Factory as Faker;

class NutritionalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 1000; $i++) {
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
                'date_of_weighing' => $faker->date(),
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
    }

        public function getHFA($num) {
        if($num == 1) {
            return 'severely stunted';
        } else if($num == 2) {
            return 'stunted';
        } else if($num == 3) {
            return 'normal';
        } else {
            return 'tall';
        }
    }
    public function getBMI($num) {
        if($num == 1) {
            return 'severely wasted';
        } else if($num == 2) {
            return 'wasted';
        } else if($num == 3) {
            return 'normal';
        } else if($num == 4) {
            return 'overweight';
        } else if($num == 5) {
            return 'obese';
        } else {
            return 'normal';
        }
    }
}
