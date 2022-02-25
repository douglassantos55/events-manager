<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCategory extends Model
{
    use HasFactory;

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'category_id');
    }
}
