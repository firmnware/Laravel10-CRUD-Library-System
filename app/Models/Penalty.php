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

    protected $casts = ['paid_date' => 'date'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}