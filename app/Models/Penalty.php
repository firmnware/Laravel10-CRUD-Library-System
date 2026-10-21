<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'member_id', 'days_late', 'fine_amount', 'status', 'paid_date'
    ];

    protected $casts = ['paid_date' => 'date'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
}
