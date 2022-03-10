<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Permission;
use App\Models\SupplierCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

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

        $event->loadMissing(['categories', 'assignees']);
        $members = $request->user()->members()->active()->get();

        return inertia('Event/View', [
            'event' => $event,
            'members' => $members,
            'categories' => SupplierCategory::all(),
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

    public function edit(Request $request, Event $event)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);

        return inertia('Event/Form', [
            'event' => $event,
            'users' => $request->user()->members,
            'save_url' => route('events.update', ['event' => $event]),
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

    public function update(Request $request, Event $event)
    {
        $this->authorize(Permission::EDIT_EVENT->value, $event);

        $validated = $request->validate([
            'title' => [
                'required',
                Rule::unique('events')->ignore($event),
            ],
            'attending_date' => ['required', 'date'],
            'budget' => ['required', 'numeric'],
        ]);

        $validated['attending_date'] = Carbon::create($validated['attending_date']);
        $event->update($validated);

        return redirect()->route('events.view', ['event' => $event]);
    }
}
