<?php

namespace App\Http\Controllers;

use App\Models\Penalty;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenaltyController extends Controller
{
    // ========== INDEX: TAMPILKAN SEMUA DENDA ==========
    public function index()
    {
        // Ambil semua penalty dari database
        $penalties = Penalty::with(['transaction.member.user', 'transaction.book'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Hitung total denda
        $totalUnpaid = $penalties->where('status', 'unpaid')->sum('fine_amount');
        $totalPaid = $penalties->where('status', 'paid')->sum('fine_amount');
        
        return view('admin.penalties.index', compact('penalties', 'totalUnpaid', 'totalPaid'));
    }

    // ========== SHOW PAY FORM: TAMPILKAN FORM BAYAR DENDA ==========
    public function showPayForm(Penalty $penalty)
    {
        // Cek apakah denda sudah lunas
        if ($penalty->status === 'paid') {
            return redirect()->route('admin.penalties.index')
                ->with('error', 'Denda ini sudah dibayar!');
        }

        return view('admin.penalties.pay', compact('penalty'));
    }

    // ========== PAY PENALTY: PROSES BAYAR DENDA ==========
    public function payPenalty(Request $request, Penalty $penalty)
    {
        // Cek apakah denda sudah lunas
        if ($penalty->status === 'paid') {
            return redirect()->route('admin.penalties.index')
                ->with('error', 'Denda ini sudah dibayar!');
        }

        DB::beginTransaction();
        try {
            // Update status denda
            $penalty->update([
                'status' => 'paid',
                'paid_date' => now()
            ]);

            DB::commit();

            return redirect()->route('admin.penalties.index')
                ->with('success', 'Denda berhasil dibayar!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('admin.penalties.index')
                ->with('error', 'Gagal membayar denda: ' . $e->getMessage());
        }
    }

    // ========== CREATE PENALTY DARI TRANSACTION (Helper) ==========
    public function createPenaltyFromTransaction(Transaction $transaction)
    {
        // Cek apakah sudah ada penalty
        if ($transaction->penalty) {
            return $transaction->penalty;
        }
        
        // Hitung denda
        $daysLate = $transaction->late_days;
        if ($daysLate <= 0) {
            return null;
        }
        
        $fineAmount = $daysLate * 2000;
        
        // Buat penalty record
        $penalty = Penalty::create([
            'transaction_id' => $transaction->id,
            'member_id' => $transaction->member_id,
            'days_late' => $daysLate,
            'fine_amount' => $fineAmount,
            'status' => 'unpaid',
            'paid_date' => null,
        ]);
        
        return $penalty;
    }
}