<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(Permission::VIEW_MEMBERS->value, User::class);

        return inertia('Member/Index', [
            'members' => $request->user()->members,
        ]);
    }
}
