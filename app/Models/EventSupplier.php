<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSupplier extends Model
{
    protected $table = 'events_suppliers';

    public $timestamps = false;

    // Custom attributes added to JSON
    protected $appends = [
        'name',
    ];

    // Hidden from JSON
    protected $hidden = [
        'supplier',
    ];

    // Eager loaded relationships
    protected $with = [
        'files',
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

    public function files(): HasMany
    {
        return $this->hasMany(ContractFile::class);
    }
}
