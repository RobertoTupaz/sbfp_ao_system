<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllSchool extends Model
{
    protected $table = 'schools';
    protected $fillable = ['school_id', 'school_name', 'district'];
    
    use HasFactory;

    public function users()
    {
        return $this->hasMany(User::class, 'school_id', 'school_id');
    }

    public function form1s()
    {
        return $this->hasMany(Form_1::class, 'school_id', 'school_id');
    }
}
