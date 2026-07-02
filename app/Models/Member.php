<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'member_code', 'join_date', 'status'];
    protected $casts = ['join_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    public function getActiveBorrowsCount()
    {
        return $this->transactions()->where('status', 'borrowed')->count();
    }

    public function canBorrow()
    {
        return $this->status === 'active' && $this->getActiveBorrowsCount() < 3;
    }

    public function hasUnpaidPenalties()
    {
        return $this->penalties()->where('status', 'unpaid')->exists();
    }
}