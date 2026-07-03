<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'member_id', 'days_late', 'fine_amount', 'status', 'paid_date'
    ];

    protected $casts = [
        'paid_date' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isUnpaid()
    {
        return $this->status === 'unpaid';
    }

    // Helper untuk mendapatkan status text
    public function getStatusTextAttribute()
    {
        return $this->isPaid() ? 'Lunas' : 'Belum Dibayar';
    }

    // Helper untuk mendapatkan status color
    public function getStatusColorAttribute()
    {
        return $this->isPaid() ? 'success' : 'danger';
    }
}