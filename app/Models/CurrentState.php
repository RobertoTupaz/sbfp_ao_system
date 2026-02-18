<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrentState extends Model
{
    protected $table = 'current_states';
    protected $fillable = [
        'state',
    ];
    use HasFactory;
}
