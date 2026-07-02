<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 
        'title', 
        'author', 
        'publisher', 
        'year', 
        'isbn', 
        'cover',      
        'stock', 
        'available_stock', 
        'description'
    ];

    // Accessor untuk mendapatkan URL cover
    public function getCoverUrlAttribute()
    {
        if ($this->cover) {
            return asset('storage/' . $this->cover);
        }
        return null;
    }

    // Helper untuk cek apakah ada cover
    public function hasCover()
    {
        return $this->cover && file_exists(storage_path('app/public/' . $this->cover));
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function isAvailable()
    {
        return $this->available_stock > 0;
    }

    public function decreaseStock($quantity = 1)
    {
        $this->available_stock -= $quantity;
        $this->save();
    }

    public function increaseStock($quantity = 1)
    {
        $this->available_stock += $quantity;
        $this->save();
    }
}