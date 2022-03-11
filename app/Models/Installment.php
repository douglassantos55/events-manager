<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    use HasFactory;

    const STATUS_PAID = 'paid';
    const STATUS_PENDING = 'pending';

    protected $fillable = [
        'value',
        'due_date',
        'status',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(EventSupplier::class, 'event_supplier_id');
    }
}
