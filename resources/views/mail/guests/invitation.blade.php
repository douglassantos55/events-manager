Hello, {{ $guest->name }}!
You've been invited for the {{ $guest->event->title }} event!

<a href="{{ route('guests.confirm', ['guest' => $guest]) }}">Confirm your presence now!</a>
