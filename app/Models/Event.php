<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'attending_date',
        'budget',
        'user_id',
    ];

    protected $with = ['assignees'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'events_users');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function agenda(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }
}
