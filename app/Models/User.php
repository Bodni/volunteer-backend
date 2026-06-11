<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\ResetPasswordNotification;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'points',
        'avatar',
        'volunteer_status',
        'is_banned' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => false,
        ];
    }
    public function rewardOrders()
    {
    return $this->hasMany(\App\Models\RewardOrder::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
public function sendPasswordResetNotification($token)
{
    $url = env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($this->email);

    $this->notify(new ResetPasswordNotification($url));
}
}
