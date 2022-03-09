<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'events_categories';

    protected $with = [
        'category',
        'suppliers',
    ];

    protected $fillable = [
        'budget',
        'event_id',
        'category_id',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupplierCategory::class, 'category_id');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(EventSupplier::class);
    }
}
