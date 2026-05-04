<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BmiVersionSimplefied extends Model
{
    protected $table = 'bmi_simplefied_version';
    protected $guarded = ['id'];
    use HasFactory;
}
