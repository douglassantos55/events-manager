<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Event::class);

        $events = $request->user()->events;

        return inertia('Events', [
            'events' => $events,
            'create_url' => route('events.create'),
        ]);
    }

    public function view(Event $event)
    {
        return inertia('Event', ['event' => $event]);
    }

    public function create()
    {
        $this->authorize('create', Event::class);

        return inertia('NewEvent', [
            'users' => User::all(),
            'save_url' => route('events.store'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => ['required', 'unique:events'],
            'attending_date' => ['required', 'date'],
            'budget' => ['required', 'numeric'],
            'users' => ['sometimes', 'array'],
        ]);

        $validated['attending_date'] = Carbon::create($validated['attending_date']);

        $event = $request->user()->events()->create($validated);
        $event->assignees()->sync($validated['users']);

        return redirect()->route('events.index');
    }
}
