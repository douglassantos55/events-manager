<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Permission;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function attach(Request $request, Event $event)
    {
        $this->authorize(Permission::CREATE_AGENDA->value, $event);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:H:i'],
            'title' => ['required'],
        ]);

        $event->agenda()->create($validated);
        return redirect()->route('events.view', ['event' => $event]);
    }
}
