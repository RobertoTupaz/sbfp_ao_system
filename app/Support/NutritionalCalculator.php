<?php

namespace App\Support;

use App\Models\BmiVersionSimplefied;
use App\Models\HfaSimplifiedVersion;

class NutritionalCalculator
{
    /** The WHO reference tables are small and fixed, so they are read once instead of once per pupil. */
    private static ?array $bmiReference = null;

    private static ?array $hfaReference = null;

    /** Forget the cached reference tables. */
    public static function flush(): void
    {
        self::$bmiReference = self::$hfaReference = null;
    }

    private static function bmiReference(int $months, ?string $sex): ?BmiVersionSimplefied
    {
        if (self::$bmiReference === null) {
            self::$bmiReference = [];
            foreach (BmiVersionSimplefied::all() as $row) {
                self::$bmiReference[$row->months.'|'.$row->sex] ??= $row;
            }
        }

        return self::$bmiReference[$months.'|'.$sex] ?? null;
    }

    private static function hfaReference(int $months, string $gender): ?HfaSimplifiedVersion
    {
        if (self::$hfaReference === null) {
            self::$hfaReference = [];
            foreach (HfaSimplifiedVersion::all() as $row) {
                self::$hfaReference[$row->month.'|'.$row->gender] ??= $row;
            }
        }

        return self::$hfaReference[$months.'|'.$gender] ?? null;
    }

    /**
     * @return array{bmi: ?float, nutritional_status: ?string, height_for_age: ?string}
     */
    public static function classify(?float $weight, ?float $height, int $ageInMonths, ?string $sex): array
    {
        $bmi = null;
        $nutritionalStatus = null;
        $heightForAge = null;

        if ($weight !== null && $height !== null && $height > 0) {
            $heightInMeters = $height / 100;
            $bmi = round($weight / ($heightInMeters * $heightInMeters), 2);

            $bmiReference = self::bmiReference($ageInMonths, $sex);

            if ($bmiReference) {
                $nutritionalStatus = match (true) {
                    $bmi < $bmiReference->sd_minus_3 => 'Severely Wasted',
                    $bmi < $bmiReference->sd_minus_2 => 'Wasted',
                    $bmi <= $bmiReference->sd_plus_2 => 'Normal',
                    $bmi <= $bmiReference->sd_plus_3 => 'Overweight',
                    default => 'Obese',
                };
            }
        }

        if ($height !== null && $ageInMonths > 0 && in_array($sex, ['m', 'f'], true)) {
            $hfaReference = self::hfaReference($ageInMonths, $sex === 'm' ? 'male' : 'female');

            if ($hfaReference) {
                $heightForAge = match (true) {
                    $height < $hfaReference->less_negative_3sd => 'Severely Stunted',
                    $height <= $hfaReference->to_less_negative_2sd => 'Stunted',
                    $height <= $hfaReference->to_positive_2sd => 'Normal',
                    default => 'Tall',
                };
            }
        }

        return [
            'bmi' => $bmi,
            'nutritional_status' => $nutritionalStatus,
            'height_for_age' => $heightForAge,
        ];
    }
}
