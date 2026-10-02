<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\Penalty;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['member.user', 'book', 'penalty']);

        // Filter berdasarkan status (overdue = virtual: borrowed + lewat jatuh tempo)
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->where('status', 'borrowed')
                    ->whereDate('due_date', '<', today());
            } elseif (in_array($request->status, ['borrowed', 'returned'])) {
                $query->where('status', $request->status);
            }
        }

        // Search: kode transaksi, kode member, nama/email/no. HP member, judul buku
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', '%'.$search.'%')
                    ->orWhereHas('book', function ($bq) use ($search) {
                        $bq->where('title', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('member', function ($mq) use ($search) {
                        $mq->where('member_code', 'like', '%'.$search.'%')
                            ->orWhereHas('user', function ($uq) use ($search) {
                                $uq->where('name', 'like', '%'.$search.'%')
                                    ->orWhere('email', 'like', '%'.$search.'%')
                                    ->orWhere('phone', 'like', '%'.$search.'%');
                            });
                    });
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        // Menambahkan query string ke pagination
        $transactions->appends(request()->query());

        return view('admin.transactions.index', compact('transactions'));
    }

    public function create()
    {
        $members = Member::with('user')->where('status', 'active')->get();
        $books = Book::with('category')->where('available_stock', '>', 0)->get();
        $categories = Category::all();

        return view('admin.transactions.create', compact('members', 'books', 'categories'));
    }

    public function store(Request $request)
    {
        $maxLoanDays = (int) config('perpustakaan.max_loan_days');

        $request->validate([
            'member_id' => 'required|exists:members,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date|before_or_equal:+'.$maxLoanDays.' days',
        ]);

        $member = Member::find($request->member_id);

        if (! $member->canBorrow()) {
            return back()->with('error', 'Member tidak dapat meminjam (maksimal '.config('perpustakaan.max_active_borrows').' buku atau status tidak aktif)');
        }

        if ($member->hasUnpaidPenalties()) {
            return back()->with('error', 'Member memiliki denda belum dibayar');
        }

        DB::beginTransaction();
        try {
            // C3: kunci baris buku, lalu cek stok. Dua peminjaman serentak
            // tidak bisa lolos bersamaan dan membuat available_stock minus.
            $book = Book::whereKey($request->book_id)->lockForUpdate()->first();

            if (! $book || ! $book->isAvailable()) {
                DB::rollback();

                return back()->with('error', 'Stok buku tidak tersedia');
            }

            // C1: kode transaksi dihasilkan lewat generator tunggal.
            $transaction = Transaction::create([
                'transaction_code' => Transaction::generateCode(),
                'member_id' => $request->member_id,
                'book_id' => $request->book_id,
                'borrow_date' => $request->borrow_date,
                'due_date' => $request->due_date,
                'status' => 'borrowed',
            ]);

            $book->decreaseStock();
            DB::commit();

            return redirect()->route('admin.transactions.index')
                ->with('success', 'Peminjaman berhasil! Kode: '.$transaction->transaction_code);
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Gagal memproses peminjaman: '.$e->getMessage());
        }
    }

    // ==========  RETURN BOOK: PROSES PENGEMBALIAN (FIXED) ==========
    public function returnBook(Transaction $transaction)
    {
        // Cek apakah buku sudah dikembalikan
        if ($transaction->status === 'returned') {
            return back()->with('error', 'Buku sudah dikembalikan');
        }

        DB::beginTransaction();
        try {
            $returnDate = Carbon::today();
            $dueDate = Carbon::parse($transaction->due_date);

            // Hitung hari keterlambatan (jika ada)
            $daysLate = 0;
            $fineAmount = 0;

            //  Cek apakah pengembalian terlambat
            if ($returnDate->gt($dueDate)) {
                $daysLate = $returnDate->diffInDays($dueDate);
                $fineAmount = $daysLate * config('perpustakaan.fine_per_day');
            }

            // Update transaksi
            $transaction->update([
                'return_date' => $returnDate,
                'status' => 'returned',
            ]);

            // Kembalikan stok buku
            $transaction->book->increaseStock();

            //  HANYA BUAT PENALTY JIKA TERLAMBAT (daysLate > 0)
            if ($daysLate > 0 && $fineAmount > 0) {
                Penalty::create([
                    'transaction_id' => $transaction->id,
                    'member_id' => $transaction->member_id,
                    'days_late' => $daysLate,
                    'fine_amount' => $fineAmount,
                    'status' => 'unpaid',
                    'paid_date' => null,
                ]);
            }

            DB::commit();

            // Buat pesan sukses
            $message = 'Buku berhasil dikembalikan';
            if ($daysLate > 0) {
                $message .= ". Terlambat $daysLate hari, denda Rp ".number_format($fineAmount, 0, ',', '.');
            } else {
                $message .= ' tepat waktu!';
            }

            return redirect()->route('admin.transactions.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Gagal memproses pengembalian: '.$e->getMessage());
        }
    }
}
