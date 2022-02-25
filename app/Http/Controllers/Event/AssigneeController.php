<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;

class AssigneeController extends Controller
{
    public function attach(Request $request, Event $event, User $assignee)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);

        if (!$request->user()->members->contains($assignee) || !$assignee->hasVerifiedEmail()) {
            return abort(403, 'Assignee is not a member or has not confirmed the invitation');
        }

        $event->assignees()->attach($assignee);
        return redirect()->route('events.view', ['event' => $event]);
    }

    public function remove(Event $event, User $assignee)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);
        $event->assignees()->detach($assignee);

        return redirect()->route('events.view', ['event' => $event]);
    }
}
