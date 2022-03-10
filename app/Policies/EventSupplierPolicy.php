<?php

namespace App\Policies;

use App\Models\EventSupplier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventSupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventSupplier  $eventSupplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editSupplier(User $user, EventSupplier $supplier)
    {
        $event = $supplier->category->event;
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventSupplier  $eventSupplier
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function removeSupplier(User $user, EventSupplier $supplier)
    {
        $event = $supplier->category->event;
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }
}
