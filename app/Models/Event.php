<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'attending_date',
        'budget',
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
}
