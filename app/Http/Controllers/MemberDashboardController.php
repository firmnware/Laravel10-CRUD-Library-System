<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Penalty;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    public function index()
    {
        // Pasca-merge: member = User yang login sendiri (tidak ada tabel members).
        $user = Auth::user();

        $activeBorrows = Loan::where('member_id', $user->id)
            ->where('status', 'borrowed')
            ->count();

        $totalBorrows = Loan::where('member_id', $user->id)->count();

        $unpaidPenalty = Penalty::where('member_id', $user->id)
            ->where('status', 'unpaid')
            ->sum('fine_amount');

<<<<<<< HEAD
=======
        // Denda berjalan dari pinjaman yang lewat jatuh tempo dan belum kembali.
        $finePerDay = (int) config('perpustakaan.fine_per_day');

        $runningPenalty = Loan::where('member_id', $user->id)
            ->where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->get()
            ->sum(fn (Loan $loan) => $loan->late_days * $finePerDay);

>>>>>>> indra/main
        $currentBorrows = Loan::with('book')
            ->where('member_id', $user->id)
            ->where('status', 'borrowed')
            ->get();

        $borrowHistory = Loan::with('book')
            ->where('member_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('member.dashboard', compact(
            'user', 'activeBorrows', 'totalBorrows',
<<<<<<< HEAD
            'unpaidPenalty', 'currentBorrows', 'borrowHistory'
=======
            'unpaidPenalty', 'runningPenalty', 'currentBorrows', 'borrowHistory'
>>>>>>> indra/main
        ));
    }

    public function myBorrows()
    {
<<<<<<< HEAD
        $borrows = Loan::with('book')
=======
        $borrows = Loan::with(['book', 'penalty'])
>>>>>>> indra/main
            ->where('member_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

<<<<<<< HEAD
        return view('member.borrows', compact('borrows'));
=======
        $finePerDay = (int) config('perpustakaan.fine_per_day');

        return view('member.borrows', compact('borrows', 'finePerDay'));
>>>>>>> indra/main
    }

    public function myPenalties()
    {
        $totalUnpaid = Penalty::where('member_id', Auth::id())
            ->where('status', 'unpaid')
            ->sum('fine_amount');
        $penalties = Penalty::with('loan.book')
            ->where('member_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

<<<<<<< HEAD
        return view('member.penalties', compact('penalties', 'totalUnpaid'));
=======
        // Denda berjalan milik member yang sedang login.
        $finePerDay = (int) config('perpustakaan.fine_per_day');

        $runningLoans = Loan::with('book')
            ->where('member_id', Auth::id())
            ->where('status', 'borrowed')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        $totalRunning = $runningLoans->sum(fn (Loan $loan) => $loan->late_days * $finePerDay);

        return view('member.penalties', compact(
            'penalties', 'totalUnpaid', 'finePerDay', 'runningLoans', 'totalRunning'
        ));
>>>>>>> indra/main
    }
}
