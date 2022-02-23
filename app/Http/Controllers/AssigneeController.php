<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\Permission;

class AssigneeController extends Controller
{
    public function remove(Event $event, User $assignee)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);
        $event->assignees()->detach($assignee);

        return redirect()->route('events.view', ['event' => $event]);
    }
}
