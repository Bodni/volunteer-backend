<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoundRequest extends Model
{
    protected $fillable = [
    'city',
    'address',
    'description',
    'photo',
    'status',
];
}