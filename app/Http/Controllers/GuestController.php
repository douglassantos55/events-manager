<?php

namespace App\Http\Controllers;

use App\Mail\GuestInvitation;
use App\Models\Event;
use App\Models\Guest;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GuestController extends Controller
{
    public function update(Request $request, Guest $guest)
    {
        $this->authorize(Permission::EDIT_GUEST->value, $guest);

        $guest->update($request->all());

        return redirect()->route('events.view', ['event' => $guest->event]);
    }

    public function delete(Guest $guest)
    {
        $this->authorize(Permission::DELETE_GUEST->value, $guest);

        $guest->delete();

        return redirect()->route('events.view', ['event' => $guest->event]);
    }

    public function invite(Request $request, Event $event)
    {
        $this->authorize(Permission::INVITE_GUEST->value, $event);

        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:\App\Models\Guest,email'],
            'relation' => ['required', Rule::in(Guest::RELATIONS)],
        ], [
            'email.unique' => 'This email has already been invited.',
        ]);

        $guest = $event->guests()->create($validator->validated());

        if ($guest) {
            Mail::send(new GuestInvitation($guest));
        }

        return redirect()->route('events.view', ['event' => $event]);
    }

    public function confirm(Guest $guest)
    {
        if ($guest->status != Guest::STATUS_PENDING) {
            throw new NotFoundHttpException();
        }

        $guest->update(['status' => Guest::STATUS_CONFIRMED]);
        return redirect()->route('guests.thanks');
    }

    public function refuse(Guest $guest)
    {
        if ($guest->status != Guest::STATUS_PENDING) {
            throw new NotFoundHttpException();
        }

        $guest->update(['status' => Guest::STATUS_REFUSED]);
        return redirect()->route('guests.thanks');
    }

    public function thanks()
    {
        return inertia('Guest/Thanks');
    }
}
