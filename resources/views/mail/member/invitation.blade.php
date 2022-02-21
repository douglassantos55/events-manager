Hello, {{ $member->name }}!

{{ $member->captain->name }} is inviting you to join his team on {{ config('app.name') }}!

<a href="{{ route('members.join') }}">Join {{ $member->captain->name }}'s team</a>
