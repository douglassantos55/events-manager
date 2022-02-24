<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;

class AssigneeController extends Controller
{
    public function add(Request $request, Event $event)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);

        $id = $request->post('assignee');
        $assignee = $request->user()->members()->whereNotNull('email_verified_at')->find($id);

        if (empty($assignee)) {
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
