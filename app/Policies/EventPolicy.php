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
    public function viewEvents()
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
    public function viewEvent(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function createEvent()
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editEvent(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can add categories to the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function addCategory(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can remove categories from the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function removeCategory(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can add suppliers to the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function addSupplier(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can edit suppliers from the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editSupplier(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can remove suppliers from the event.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function removeSupplier(User $user, Event $event)
    {
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }
}
