<?php

namespace App\Http\Controllers;

use App\Models\HfaSimplifiedVersion;
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
}
