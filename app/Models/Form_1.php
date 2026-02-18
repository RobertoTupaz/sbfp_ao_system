<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form_1 extends Model
{
    protected $table = 'form_1';

    protected $fillable = [
        'school_id',
        'school_year',
        'survey_state',
        'name',
        'sex',
        'grade',
        'section',
        'date_of_birth',
        'date_of_weighing_or_measuring',
        'age_in_years',
        'age_in_months',
        'weight',
        'height',
        'bmi_for_6_years_and_above',
        'bmi_a',
        'hfa',
        'in_4ps',
        'ip',
        'pardo',
        'dewormed',
        'parent_consent_milk',
        'beneficiary_of_sbfp_in_previous_year',
    ];
    
    use HasFactory;

    public function school()
    {
        return $this->belongsTo(AllSchool::class, 'school_id', 'school_id');
    }
}
