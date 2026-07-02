<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Penalty;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $member = $user->member;
        
        $activeBorrows = Transaction::where('member_id', $member->id)
            ->where('status', 'borrowed')
            ->count();
        
        $totalBorrows = Transaction::where('member_id', $member->id)->count();
        
        $unpaidPenalty = Penalty::where('member_id', $member->id)
            ->where('status', 'unpaid')
            ->sum('fine_amount');
        
        $currentBorrows = Transaction::with('book')
            ->where('member_id', $member->id)
            ->where('status', 'borrowed')
            ->get();
        
        $borrowHistory = Transaction::with('book')
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('member.dashboard', compact(
            'member', 'activeBorrows', 'totalBorrows', 
            'unpaidPenalty', 'currentBorrows', 'borrowHistory'
        ));
    }

    public function myBorrows()
    {
        $member = Auth::user()->member;
        $borrows = Transaction::with('book')
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('member.borrows', compact('borrows'));
    }

    public function myPenalties()
    {
        $member = Auth::user()->member;
        $penalties = Penalty::with('transaction.book')
            ->where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('member.penalties', compact('penalties'));
    }
}