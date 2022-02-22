<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function captain()
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function events()
    {
        if ($this->captain) {
            return $this->captain->events();
        }
        return $this->hasMany(Event::class);
    }

    public function roles()
    {
        if ($this->captain) {
            return $this->captain->roles();
        }
        return $this->hasMany(Role::class);
    }

    public function members()
    {
        if ($this->captain) {
            return $this->captain->members();
        }
        return $this->hasMany(User::class, 'captain_id');
    }

    public function plan(): Attribute
    {
        return new Attribute(
            get: function ($value) {
                if ($this->captain) {
                    return $this->captain->plan;
                }
                if ($value instanceof Plan) {
                    return $value;
                }
                return Plan::create($this, $value);
            }
        );
    }
}
