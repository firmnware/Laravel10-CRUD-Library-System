<?php

namespace App\Http\Controllers;

use App\Models\Penalty;
use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    public function index()
    {
        $penalties = Penalty::with(['transaction.member.user', 'transaction.book'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.penalties.index', compact('penalties'));
    }

    public function payPenalty(Penalty $penalty)
    {
        if ($penalty->status === 'paid') {
            return back()->with('error', 'Denda sudah dibayar');
        }

        $penalty->update([
            'status' => 'paid',
            'paid_date' => now()
        ]);

        return redirect()->route('admin.penalties.index')->with('success', 'Denda berhasil dibayar');
    }
}