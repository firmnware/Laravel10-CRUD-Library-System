<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code', 'member_id', 'book_id', 
        'borrow_date', 'due_date', 'return_date', 'status'
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    // ========== RELATIONSHIPS ==========
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function penalty()
    {
        return $this->hasOne(Penalty::class);
    }

    // ========== HITUNG DENDA REAL-TIME ==========
    public function calculateFine()
    {
        if ($this->return_date && $this->due_date) {
            $daysLate = $this->return_date->diffInDays($this->due_date, false);
            if ($daysLate > 0) {
                return $daysLate * 2000;
            }
        }
        return 0;
    }

    /**
     * Hitung denda real-time tanpa harus return_date
     * Menghitung denda berdasarkan hari ini vs due_date
     */
    public function getCurrentFineAttribute()
    {
        if ($this->status === 'returned') {
            return $this->calculateFine(); // Pakai denda yang sudah dihitung
        }

        $now = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();
        
        if ($now->gt($dueDate)) {
            $daysLate = $now->diffInDays($dueDate);
            return $daysLate * 2000;
        }
        
        return 0;
    }

    /**
     * Cek apakah transaksi ini memiliki denda (real-time)
     */
    public function getHasFineAttribute()
    {
        if ($this->status === 'returned') {
            return $this->penalty()->exists();
        }
        
        $now = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();
        
        return $now->gt($dueDate);
    }

    /**
     * Get jumlah hari terlambat (real-time)
     */
    public function getLateDaysAttribute()
    {
        if ($this->status === 'returned') {
            if ($this->penalty) {
                return $this->penalty->days_late;
            }
            return 0;
        }

        $now = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();
        
        if ($now->gt($dueDate)) {
            return $now->diffInDays($dueDate);
        }
        
        return 0;
    }

    /**
     * Get status denda (real-time)
     */
    public function getFineStatusAttribute()
    {
        if ($this->status === 'returned') {
            if ($this->penalty) {
                return $this->penalty->status;
            }
            return 'no_fine';
        }

        if ($this->has_fine) {
            return 'unpaid';
        }
        
        return 'no_fine';
    }

    /**
     * Get text status denda
     */
    public function getFineStatusTextAttribute()
    {
        $status = $this->fine_status;
        
        if ($status === 'paid') {
            return 'Lunas';
        } elseif ($status === 'unpaid') {
            return 'Belum Dibayar';
        } else {
            return 'Tidak Ada Denda';
        }
    }

    /**
     * Get badge color for fine status
     */
    public function getFineStatusColorAttribute()
    {
        $status = $this->fine_status;
        
        if ($status === 'paid') {
            return 'success';
        } elseif ($status === 'unpaid') {
            return 'danger';
        } else {
            return 'secondary';
        }
    }

    // ========== STATUS SISA HARI ==========
    public function getRemainingDaysAttribute()
    {
        if ($this->status === 'returned') {
            return 999;
        }
        
        $now = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($this->due_date)->startOfDay();
        
        $diffInDays = $now->diffInDays($dueDate, false);
        
        if ($diffInDays < 0) {
            return (int) $diffInDays;
        }
        
        return (int) $diffInDays;
    }

    public function getStatusColorAttribute()
    {
        if ($this->status === 'returned') {
            return 'success';
        }
        
        $remaining = $this->remaining_days;
        
        if ($remaining < 0) {
            return 'danger';
        } elseif ($remaining <= 1) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    public function getStatusTextAttribute()
    {
        if ($this->status === 'returned') {
            return 'Sudah Dikembalikan';
        }
        
        $remaining = $this->remaining_days;
        
        if ($remaining < 0) {
            $daysLate = abs($remaining);
            if ($daysLate == 1) {
                return 'Terlambat 1 hari';
            }
            return 'Terlambat ' . $daysLate . ' hari';
        } elseif ($remaining == 0) {
            return 'Jatuh Tempo Hari Ini! ⚠️';
        } elseif ($remaining == 1) {
            return 'Sisa 1 hari lagi ⏰';
        } else {
            return 'Sisa ' . $remaining . ' hari';
        }
    }

    public function getWarningMessageAttribute()
    {
        if ($this->status === 'returned') {
            return null;
        }
        
        $remaining = $this->remaining_days;
        
        if ($remaining < 0) {
            $daysLate = abs($remaining);
            $fine = $daysLate * 2000;
            return '⚠️ Terlambat ' . $daysLate . ' hari! Denda Rp ' . number_format($fine, 0, ',', '.');
        } elseif ($remaining == 0) {
            return '⚠️ Segera kembalikan buku! Jatuh tempo hari ini.';
        } elseif ($remaining <= 1) {
            return '⏰ Jangan lupa kembalikan buku besok.';
        } elseif ($remaining <= 3) {
            return '⏰ Jangan lupa kembalikan buku dalam ' . $remaining . ' hari.';
        }
        
        return null;
    }
}