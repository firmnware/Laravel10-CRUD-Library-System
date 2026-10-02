<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'member_code', 'join_date', 'status'];

    protected $casts = ['join_date' => 'date'];

    /**
     * Generator tunggal kode member (C2).
     *
     * Sebelumnya kode dihasilkan di 3 tempat dengan formula berbeda
     * (RegisteredUserController memakai User::count(), MemberController
     * memakai Member::count()+1) sehingga bisa tabrakan. Seeder mengisi kode
     * secara eksplisit, jadi hook ini hanya menangani kode yang kosong.
     */
    protected static function booted(): void
    {
        static::creating(function (Member $member) {
            if (! empty($member->member_code)) {
                return;
            }

            $nextId = (int) static::max('id') + 1;

            // Loop kecil: kalau kode hasil max(id)+1 sudah terpakai (mis. setelah
            // penghapusan member), naikkan sampai benar-benar bebas.
            do {
                $code = 'MBR-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
                $nextId++;
            } while (static::where('member_code', $code)->exists());

            $member->member_code = $code;
        });
    }

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
        return $this->status === 'active'
            && $this->getActiveBorrowsCount() < config('perpustakaan.max_active_borrows');
    }

    public function hasUnpaidPenalties()
    {
        return $this->penalties()->where('status', 'unpaid')->exists();
    }
}
