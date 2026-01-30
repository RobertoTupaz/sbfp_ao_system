<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwappedPupils extends Model
{
    use HasFactory;
    protected $table = "swapped_pupils";
    protected $guarded = ['id', 'swap_date', 'created_at','updated_at'];
}
