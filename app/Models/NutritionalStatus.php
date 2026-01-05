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
    ];

    protected $casts = [
        'birthday' => 'date',
        'weight' => 'float',
        'height' => 'float',
        'bmi' => 'float',
        'age_years' => 'integer',
        'age_months' => 'integer',
    ];
}
