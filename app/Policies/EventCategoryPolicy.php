<?php

namespace App\Policies;

use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can add suppliers to category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventCategory $category
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function addSupplier(User $user, EventCategory $category)
    {
        return in_array($category->event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can delete the category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\EventCategory $category
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function removeCategory(User $user, EventCategory $category)
    {
        return in_array($category->event->user_id, [$user->id, $user->captain?->id]);
    }
}
