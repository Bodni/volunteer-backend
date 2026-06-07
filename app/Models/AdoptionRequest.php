<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdoptionRequest extends Model
{
    protected $fillable = [
        'animal_name',
        'name',
        'phone',
        'message',
        'status',
    ];
}