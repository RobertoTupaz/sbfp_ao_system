<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionalStatus extends Model
{
    use HasFactory;

    protected $table = 'nutritional_statuses';
    protected $fillable = [
        'full_name',
        'birthday',
        'sex',
        'weight',
        'height',
        'age_years',
        'age_months',
        'bmi',
        'nutritional_status',
        'height_for_age',
        'date_of_weighing',
        'grade',
        'section',
        '4ps',
        'ip',
        'pardo',
        'dewormed',
        'parent_consent_milk',
        'sbfp_previous_beneficiary',
    ];

    protected $casts = [
        'birthday' => 'date',
        'date_of_weighing' => 'date',
        'weight' => 'float',
        'height' => 'float',
        'bmi' => 'float',
        'age_years' => 'integer',
        'age_months' => 'integer',
    ];
}
