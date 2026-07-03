<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index()
    {
        //  PAGINATION 10 MEMBER PER HALAMAN
        $members = Member::with('user')->latest()->paginate(10);
        return view('admin.members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.members.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
            'status' => 'active',
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        Member::create([
            'user_id' => $user->id,
            'member_code' => 'MBR-' . str_pad(Member::count() + 1, 5, '0', STR_PAD_LEFT),
            'join_date' => now(),
            'status' => 'active'
        ]);

        return redirect()->route('admin.members.index')->with('success', 'Member berhasil ditambahkan');
    }

    public function toggleStatus(Member $member)
    {
        $newStatus = $member->status === 'active' ? 'blocked' : 'active';
        $member->update(['status' => $newStatus]);
        $member->user->update(['status' => $newStatus === 'active' ? 'active' : 'inactive']);

        $message = $newStatus === 'active' ? 'Member diaktifkan' : 'Member diblokir';
        return redirect()->route('admin.members.index')->with('success', $message);
    }
}