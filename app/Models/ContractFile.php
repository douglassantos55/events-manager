<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(EventSupplier::class);
    }
}
