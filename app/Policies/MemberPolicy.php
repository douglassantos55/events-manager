<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MemberPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewMembers()
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $member
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editMember(User $user, User $member)
    {
        return in_array($member->captain_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function inviteMember()
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $member
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deleteMember(User $user, User $member)
    {
        return $member->id !== $user->id && !is_null($member->captain_id) && in_array($member->captain_id, [$user->id, $user->captain?->id]);
    }
}
