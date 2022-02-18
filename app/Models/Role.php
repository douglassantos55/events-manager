<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Role extends Model
{
    use HasFactory;

    /**
     * Array of allowed actions, e.g,
     * 'create_event', 'update_agenda'
     *
     * @var Collection
     */
    private $permissions;

    protected $fillable = [
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'collection',
    ];

    public function can(string $permission)
    {
        return $this->permissions->has($permission);
    }
}
