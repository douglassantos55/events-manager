<?php

namespace App\Policies;

use App\Models\Installment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstallmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the installment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Installment  $installment
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function editInstallment(User $user, Installment $installment)
    {
        $event = $installment->supplier->category->event;
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }

    /**
     * Determine whether the user can delete the installment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Installment  $installment
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function removeInstallment(User $user, Installment $installment)
    {
        $event = $installment->supplier->category->event;
        return in_array($event->user_id, [$user->id, $user->captain?->id]);
    }
}
