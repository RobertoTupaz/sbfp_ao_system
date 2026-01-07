<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HfaSimplifiedVersion extends Model
{
    use HasFactory;

    protected $table = 'hfa_simplefied_version';

    protected $guarded = ['id'];
}
