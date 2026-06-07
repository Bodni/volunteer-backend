<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
    'title',
    'description',
    'photo',
    'status',
    'assigned_to',
    'points',
];

public function assignee()
{
    return $this->belongsTo(User::class, 'assigned_to');
}
}