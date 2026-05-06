<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimarySecondaryBeneficiaries extends Model
{
    use HasFactory;

    protected $table = 'primary_secondary_beneficiaries';

    protected $guarded = ['id'];

    protected $casts = [
        'all_kinder' => 'boolean',
        'all_grade_1' => 'boolean',
        'all_grade_2' => 'boolean',
        'all_grade_3' => 'boolean',
        'severely_wasted' => 'boolean',
        'wasted' => 'boolean',
        'normal_weight' => 'boolean',
        'overweight_obese' => 'boolean',
        'severely_stunted' => 'boolean',
        'stunted' => 'boolean',
        'normal_height' => 'boolean',
        'tall' => 'boolean',
        '_4ps' => 'boolean',
        'ip' => 'boolean',
        'pardo' => 'boolean',
    ];
}
