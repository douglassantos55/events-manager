<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSupplier extends Model
{
    public $incrementing = true;

    public $timestamps = false;

    protected $table = 'events_suppliers';

    // Custom attributes added to JSON
    protected $appends = [
        'name',
    ];

    // Hidden from JSON
    protected $hidden = [
        'supplier',
    ];

    protected $fillable = [
        'value',
        'status',
        'supplier_id',
    ];

    public function name(): Attribute
    {
        return new Attribute(
            get: fn () => $this->supplier->name
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
