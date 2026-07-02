@extends('layouts.app')

@section('title', 'Katalog Buku')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-book me-2"></i> Katalog Buku Perpustakaan</h5>
    </div>
    <div class="card-body">
        @if($books->count() > 0)
            <div class="row">
                @foreach($books as $book)
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <!-- Cover -->
                        <div style="height: 220px; overflow: hidden; background: #f8f9fa;">
                            @if($book->cover && Storage::disk('public')->exists($book->cover))
                                <img src="{{ asset('storage/' . $book->cover) }}" 
                                     alt="{{ $book->title }}" 
                                     class="card-img-top" 
                                     style="width: 100%; height: 220px; object-fit: cover;">
                            @else
                                <div class="d-flex align-items-center justify-content-center" 
                                     style="height: 220px; background: #e9ecef;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-book fa-4x"></i>
                                        <p class="mb-0 small">Tidak ada cover</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            <h6 class="card-title">{{ Str::limit($book->title, 40) }}</h6>
                            <p class="text-muted small mb-1">
                                <i class="fas fa-user me-1"></i> {{ $book->author ?? 'Tidak diketahui' }}
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-tag me-1"></i> {{ $book->category->name ?? 'Tanpa Kategori' }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-{{ $book->available_stock > 0 ? 'success' : 'danger' }}">
                                    {{ $book->available_stock > 0 ? 'Tersedia' : 'Habis' }}
                                </span>
                                <small class="text-muted">Stok: {{ $book->available_stock }}</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="{{ route('member.books.show', $book) }}" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-info-circle me-1"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- menampilkan total buku  -->
            <div class="mt-3 text-muted text-center">
                <i class="fas fa-info-circle me-1"></i> 
                Menampilkan <strong>{{ $books->count() }}</strong> buku di katalog
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada buku di katalog</p>
            </div>
        @endif
    </div>
</div>
@endsection