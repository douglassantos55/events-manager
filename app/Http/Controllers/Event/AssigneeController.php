<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssigneeController extends Controller
{
    public function attach(Request $request, Event $event)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);

        $user = $request->user();

        $validated = $request->validate([
            'assignee' => [
                'required',
                Rule::exists('App\Models\User', 'id')->whereIn('captain_id', [$user->id, $user->captain?->id])->whereNotNull('email_verified_at')
            ],
        ]);

        if (!$event->assignees->contains($validated['assignee'])) {
            $event->assignees()->attach($validated['assignee']);
        }

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function remove(Event $event, User $assignee)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);
        $event->assignees()->detach($assignee);

        return redirect()->route('events.view', ['event' => $event]);
    }
}
