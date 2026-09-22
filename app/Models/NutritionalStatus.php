<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NutritionalStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nutritional_statuses';

    protected $fillable = [
        'full_name',
        'last_name',
        'first_name',
        'suffix_name',
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
        '_4ps',
        'ip',
        'pardo',
        'dewormed',
        'parent_consent_milk',
        'sbfp_previous_beneficiary',
        'isBeneficiary',
        'deleted_by',
    ];

    protected $casts = [
        'birthday' => 'date',
        'date_of_weighing' => 'date',
        'weight' => 'float',
        'height' => 'float',
        'bmi' => 'float',
        'age_years' => 'integer',
        'age_months' => 'integer',
        '_4ps' => 'boolean',
        'ip' => 'boolean',
        'pardo' => 'boolean',
        'dewormed' => 'boolean',
        'parent_consent_milk' => 'boolean',
        'sbfp_previous_beneficiary' => 'boolean',
        'isBeneficiary' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (NutritionalStatus $pupil) {
            if (! $pupil->isForceDeleting() && ! $pupil->deleted_by && auth()->check()) {
                $pupil->deleted_by = auth()->id();
                $pupil->saveQuietly();
            }
        });
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
