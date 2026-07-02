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
}