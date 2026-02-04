<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolsDistrict extends Model
{
    protected $table = 'school';
    protected $fillable = ['name', 'district_id'];
    use HasFactory;
}
