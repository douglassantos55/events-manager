<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'status',
        'relation',
    ];

    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PENDING = 'pending';
    const STATUS_REFUSED = 'refused';

    const RELATIONS = [
        'friend',
        'parent',
        'relative',
        'grandparent',
        'colleague',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
