<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class user extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    public function guardName(){
        return "web";
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'username',
        'email',
        'password',
        'avatar_class',
        'email_verified_at',
        'change_next_login',
        'failed_attempts'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     /**
     * Obliga a tomar la nueva estroctura de contenido del correo restablecimiento de contraseña
     * nueva ES
     *contenido original: vendor\laravel\framework\src\Illuminate\Auth\Notifications\ResetPassword.php
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_user', 'user_id', 'notification_id');
    }
    
}
