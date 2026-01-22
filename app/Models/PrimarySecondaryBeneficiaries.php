<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimarySecondaryBeneficiaries extends Model
{
    use HasFactory;

    protected $table = 'primary_secondary_beneficiaries';

    protected $guarded = ['id'];
}
