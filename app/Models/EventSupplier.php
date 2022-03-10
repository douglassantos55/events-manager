<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSupplier extends Model
{
    use HasFactory;

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
        'installments',
    ];

    protected $fillable = [
        'value',
        'status',
        'supplier_id',
    ];

    public function canCreateInstallment(Installment $installment)
    {
        $sum = $this->installments()->sum('value');
        return ($sum + $installment->value) <= $this->value;
    }

    public function name(): Attribute
    {
        return new Attribute(
            get: fn () => $this->supplier->name
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractFile::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }
}
