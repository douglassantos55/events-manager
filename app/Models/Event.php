<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'attending_date',
        'budget',
        'user_id',
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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(SupplierCategory::class, 'events_categories')->withPivot(['budget']);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'events_suppliers')->withPivot(['status', 'value']);
    }

    public function getSuppliersFor(int $categoryId)
    {
        return $this->suppliers->where('category_id', $categoryId);
    }
}
