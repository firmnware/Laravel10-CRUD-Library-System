<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Member;
use App\Models\Book;
use App\Models\Category;
use App\Models\Penalty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // ========== INDEX: TAMPILKAN SEMUA TRANSAKSI DENGAN STATUS DENDA ==========
    public function index()
    {
        $transactions = Transaction::with(['member.user', 'book', 'penalty'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('admin.transactions.index', compact('transactions'));
    }

    // ========== CREATE: FORM PEMINJAMAN ==========
    public function create()
    {
        $members = Member::where('status', 'active')->get();
        $books = Book::with('category')->where('available_stock', '>', 0)->get();
        $categories = Category::all();
        return view('admin.transactions.create', compact('members', 'books', 'categories'));
    }

    // ========== STORE: PROSES PEMINJAMAN ==========
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
        ]);

        $member = Member::find($request->member_id);
        $book = Book::find($request->book_id);

        if (!$member->canBorrow()) {
            return back()->with('error', 'Member tidak dapat meminjam (maksimal 3 buku atau status tidak aktif)');
        }

        if ($member->hasUnpaidPenalties()) {
            return back()->with('error', 'Member memiliki denda belum dibayar');
        }

        if (!$book->isAvailable()) {
            return back()->with('error', 'Stok buku tidak tersedia');
        }

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'transaction_code' => 'TRX-' . date('Ymd') . '-' . rand(100, 999),
                'member_id' => $request->member_id,
                'book_id' => $request->book_id,
                'borrow_date' => $request->borrow_date,
                'due_date' => $request->due_date,
                'status' => 'borrowed'
            ]);

            $book->decreaseStock();
            DB::commit();
            
            return redirect()->route('admin.transactions.index')
                ->with('success', 'Peminjaman berhasil! Kode: ' . $transaction->transaction_code);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses peminjaman: ' . $e->getMessage());
        }
    }

    // ========== RETURN BOOK: PROSES PENGEMBALIAN ==========
    public function returnBook(Transaction $transaction)
    {
        if ($transaction->status === 'returned') {
            return back()->with('error', 'Buku sudah dikembalikan');
        }

        DB::beginTransaction();
        try {
            $returnDate = Carbon::today();
            $daysLate = max(0, $returnDate->diffInDays($transaction->due_date, false));
            
            $transaction->update([
                'return_date' => $returnDate,
                'status' => 'returned'
            ]);

            $transaction->book->increaseStock();

            //  Buat atau update penalty
            if ($daysLate > 0) {
                $fineAmount = $daysLate * 2000;
                
                // Cek apakah sudah ada penalty
                $penalty = Penalty::where('transaction_id', $transaction->id)->first();
                
                if ($penalty) {
                    // Update penalty yang sudah ada
                    $penalty->update([
                        'days_late' => $daysLate,
                        'fine_amount' => $fineAmount,
                    ]);
                } else {
                    // Buat penalty baru
                    Penalty::create([
                        'transaction_id' => $transaction->id,
                        'member_id' => $transaction->member_id,
                        'days_late' => $daysLate,
                        'fine_amount' => $fineAmount,
                        'status' => 'unpaid',
                        'paid_date' => null,
                    ]);
                }
            }

            DB::commit();

            $message = 'Buku berhasil dikembalikan';
            if ($daysLate > 0) {
                $message .= ". Terlambat $daysLate hari, denda Rp " . number_format($fineAmount, 0, ',', '.');
            }

            return redirect()->route('admin.transactions.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage());
        }
    }

    // ========== SHOW: DETAIL TRANSAKSI ==========
    public function show(Transaction $transaction)
    {
        return view('admin.transactions.show', compact('transaction'));
    }
}