<?php

namespace App\Policies;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GuestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Guest  $guest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editGuest(User $user, Guest $guest)
    {
        return in_array($guest->event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Guest  $guest
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteGuest(User $user, Guest $guest)
    {
        return in_array($guest->event->user_id, [$user->id, $user->captain?->id]);
    }
}
