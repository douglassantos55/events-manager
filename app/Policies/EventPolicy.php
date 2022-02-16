<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny()
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Event $event)
    {
        return $event->user === $user || in_array($user, $event->assignees);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        $hasLimit = $user->events()->count() < intval($user->max_events);
        $hasPermission = in_array($user->role, ['assistant', 'administrator']);

        return $hasPermission && $hasLimit;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Event $event)
    {
        return in_array($user->role, ['assistant', 'administrator']) && $event->user === $user;

    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Event $event)
    {
        return $user->role === 'administrator' && $event->user === $user;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Event $event)
    {
        return $user->role === 'administrator' && $event->user === $user;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Event $event)
    {
        return $user->role === 'administrator' && $event->user === $user;
    }
}
