<?php

namespace App\Http\Controllers;

use App\Models\HfaSimplifiedVersion;
use App\Models\BmiVersionSimplefied;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

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
}
