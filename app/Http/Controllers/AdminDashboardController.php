<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\Penalty;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalBooks = Book::count();
        $totalMembers = Member::count();
        $activeBorrows = Transaction::where('status', 'borrowed')->count();
        $totalPenalties = Penalty::where('status', 'unpaid')->sum('fine_amount');
        
        $recentTransactions = Transaction::with(['member.user', 'book'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalBooks', 'totalMembers', 'activeBorrows', 
            'totalPenalties', 'recentTransactions'
        ));
    }
}