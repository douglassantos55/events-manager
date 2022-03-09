<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'events_categories';

    // Eager loaded relationships
    protected $with = [
        'suppliers',
    ];

    protected $fillable = [
        'budget',
        'event_id',
        'category_id',
    ];

    // Custom attributes added to JSON
    protected $appends = [
        'name',
        'all_suppliers',
    ];

    // Hidden from JSON
    protected $hidden = [
        'category',
    ];

    public function name(): Attribute
    {
        return new Attribute(
            get: fn () => $this->category->name
        );
    }

    public function allSuppliers(): Attribute
    {
        return new Attribute(
            get: fn () => $this->category->suppliers
        );
    }

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
