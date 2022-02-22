<?php

namespace App\Http\Controllers;

use App\Mail\MemberInvitation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permission::VIEW_MEMBERS->value, User::class);

        return inertia('Member/Index', [
            'members' => $request->user()->members,
        ]);
    }

    public function invite(Request $request)
    {
        $this->authorize(Permission::INVITE_MEMBER->value, User::class);

        return inertia('Member/Invite', [
            'roles' => $request->user()->roles,
            'save_url' => route('members.store'),
        ]);
    }

    public function join(User $member)
    {
        if ($member->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        return inertia('Member/Join', [
            'member' => $member,
            'save_url' => route('members.save', ['member' => $member]),
        ]);
    }

    public function save(Request $request, User $member)
    {
        if ($member->hasVerifiedEmail()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name' => ['required'],
            'password' => ['required', 'confirmed'],
        ]);

        $member->update([
            ...$validated,
            'password' => bcrypt('password'),
        ]);

        $member->markEmailAsVerified();

        Auth::login($member);
        return redirect()->route('dashboard');
    }

    public function store(Request $request)
    {
        $this->authorize(Permission::INVITE_MEMBER->value, User::class);

        $validated = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'role_id' => ['required', 'exists:App\Models\Role,id'],
        ]);

        $member = $request->user()->members()->create([
            ...$validated,
            'password' => bcrypt('password'),
        ]);

        Mail::send(new MemberInvitation($member));
        return redirect()->route('members.index');
    }
}
