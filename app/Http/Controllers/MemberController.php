<?php

namespace App\Http\Controllers;

use App\Mail\MemberInvitation;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permission::VIEW_MEMBERS->value, User::class);

        return inertia('Member/Index', [
            'members' => $request->user()->members()->with('role')->get(),
            'invite_url' => route('members.invite'),
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

    public function edit(Request $request, User $member)
    {
        $this->authorize(Permission::EDIT_MEMBER->value, $member);

        return inertia('Member/Form', [
            'member' => $member,
            'roles' => $request->user()->roles,
            'save_url' => route('members.update', ['member' => $member]),
            'destroy_url' => route('members.destroy', ['member' => $member]),
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

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where('user_id', $user->captain?->id || $user->id),
            ],
        ]);

        $member = $user->members()->create([
            ...$validated,
            'password' => bcrypt('password'),
        ]);

        Mail::send(new MemberInvitation($member));
        return redirect()->route('members.index');
    }

    public function update(Request $request, User $member)
    {
        $this->authorize(Permission::EDIT_MEMBER->value, $member);

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where('user_id', $user->captain?->id || $user->id),
            ],
        ]);

        $member->update($validated);
        return redirect()->route('members.index');
    }

    public function destroy(User $member)
    {
        $this->authorize(Permission::DELETE_MEMBER->value, $member);

        $member->delete();
        return redirect()->route('members.index');
    }
}
