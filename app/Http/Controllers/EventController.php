<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

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

    public function create()
    {
        $this->authorize('create', Event::class);

        return inertia('NewEvent');
    }
}
