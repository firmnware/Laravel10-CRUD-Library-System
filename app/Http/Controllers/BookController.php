<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    // ========== ADMIN: LIHAT SEMUA BUKU ==========
    public function index()
    {
        $books = Book::with('category')->latest()->get();
        return view('admin.books.index', compact('books'));
    }

    // ========== MEMBER: LIHAT KATALOG BUKU (DENGAN FILTER KATEGORI) ==========
    public function memberIndex(Request $request)
    {
        // Ambil semua kategori untuk dropdown
        $categories = Category::all();
        
        // Query buku dengan relasi kategori
        $query = Book::with('category');
        
        // Filter berdasarkan kategori jika ada
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }
        
        // Ambil data buku
        $books = $query->latest()->get();
        
        // Kirim data ke view
        return view('member.books.index', compact('books', 'categories'));
    }

    // ========== TAMPILKAN FORM TAMBAH ==========
    public function create()
    {
        $categories = Category::all();
        return view('admin.books.create', compact('categories'));
    }

    // ========== SIMPAN BUKU BARU ==========
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books',
            'stock' => 'required|integer|min:1',
            'category_id' => 'nullable|exists:categories,id',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        $data['available_stock'] = $request->stock;

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = time() . '-' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('covers', $filename, 'public');
            $data['cover'] = $path;
        }

        Book::create($data);
        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil ditambahkan');
    }

    // ========== DETAIL BUKU ==========
    public function show(Book $book)
    {
        if (auth()->user()->isMember) {
            return view('member.books.show', compact('book'));
        }
        return view('admin.books.show', compact('book'));
    }

    // ========== FORM EDIT ==========
    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('admin.books.edit', compact('book', 'categories'));
    }

    // ========== UPDATE BUKU ==========
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn,' . $book->id,
            'stock' => 'required|integer|min:1',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        $diff = $request->stock - $book->stock;
        $data['available_stock'] = $book->available_stock + $diff;

        if ($request->hasFile('cover')) {
            if ($book->cover && Storage::disk('public')->exists($book->cover)) {
                Storage::disk('public')->delete($book->cover);
            }
            
            $file = $request->file('cover');
            $filename = time() . '-' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('covers', $filename, 'public');
            $data['cover'] = $path;
        }

        $book->update($data);
        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil diupdate');
    }

    // ========== HAPUS BUKU ==========
    public function destroy(Book $book)
    {
        if ($book->cover && Storage::disk('public')->exists($book->cover)) {
            Storage::disk('public')->delete($book->cover);
        }
        
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Buku berhasil dihapus');
    }
}