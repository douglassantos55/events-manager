<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permission::VIEW_EVENTS->value, Event::class);

        return inertia('Event/Index', [
            'events' => $request->user()->events,
        ]);
    }

    public function view(Request $request, Event $event)
    {
        $this->authorize(Permission::VIEW_EVENT->value, $event);

        $members = $request->user()->members()->whereNotNull('email_verified_at')->get();

        return inertia('Event/View', [
            'event' => $event,
            'members' => $members,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize(Permission::CREATE_EVENT->value, Event::class);

        return inertia('Event/Form', [
            'users' => $request->user()->members,
            'save_url' => route('events.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize(Permission::CREATE_EVENT->value, Event::class);

        $validated = $request->validate([
            'title' => ['required', 'unique:events'],
            'attending_date' => ['required', 'date'],
            'budget' => ['required', 'numeric'],
            'users' => ['sometimes', 'array'],
        ]);

        $validated['attending_date'] = Carbon::create($validated['attending_date']);

        $event = $request->user()->events()->create($validated);
        $event->assignees()->sync(array_column($validated['users'], 'id'));

        return redirect()->route('events.index');
    }
}
