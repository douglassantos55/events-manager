Hello, {{ $guest->name }}!

You've been invited to the {{ $guest->event->title }} event!
It's really important that you respond to this invitation.

<a href="{{ route('guests.confirm', ['guest' => $guest]) }}">Confirm my presence</a>
<a href="{{ route('guests.refuse', ['guest' => $guest]) }}">I'm afraid I won't make it </a>
