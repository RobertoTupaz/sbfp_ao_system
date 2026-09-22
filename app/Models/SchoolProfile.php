<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'school_head_name', 'school_focal_name', 'school_email'];

    public function school()
    {
        return $this->belongsTo(AllSchool::class, 'school_id', 'school_id');
    }
}
