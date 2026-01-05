<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'district';
    protected $guarded = [];

    use HasFactory;

    public function schools()
    {
        return $this->hasMany(School::class, 'district_id');
    }
}
