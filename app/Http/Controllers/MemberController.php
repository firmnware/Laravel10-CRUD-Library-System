<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::with('user');

        // Filter berdasarkan status member
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search berdasarkan kode member, nama, email, no. telepon
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('member_code', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    });
            });
        }

        $members = $query->latest()->paginate(10);

        // Menambahkan query string ke pagination
        $members->appends(request()->query());

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
            'phone' => 'nullable|string|max:15',
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

        // Kode member dihasilkan otomatis oleh Member::booted() (C2)
        // agar tidak tabrakan dengan formula berbeda di tempat lain.
        Member::create([
            'user_id' => $user->id,
            'join_date' => now(),
            'status' => 'active',
        ]);

        return redirect()->route('admin.members.index')->with('success', 'Member berhasil ditambahkan');
    }

    public function toggleStatus(Member $member)
    {
        // C6: mapping dua kolom harus sinkron —
        //   members.status = active  <-> users.status = active
        //   members.status = blocked  <-> users.status = inactive
        // (users.status inilah yang dicegah MemberMiddleware/AdminMiddleware.)
        $newStatus = $member->status === 'active' ? 'blocked' : 'active';
        $member->update(['status' => $newStatus]);
        $member->user->update(['status' => $newStatus === 'active' ? 'active' : 'inactive']);

        $message = $newStatus === 'active' ? 'Member diaktifkan' : 'Member diblokir';

        return redirect()->route('admin.members.index')->with('success', $message);
    }
}
