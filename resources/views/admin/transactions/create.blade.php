@extends('layouts.app')

@section('title', 'Pinjam Buku')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-hand-holding me-2"></i> Form Peminjaman Buku</h5>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('admin.transactions.store') }}" method="POST" id="borrowForm">
            @csrf
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Cari Member <span class="text-danger">*</span></label>
                    <select name="member_id" id="member_select" class="form-control @error('member_id') is-invalid @enderror" required>
                        <option value="">-- Ketik nama, kode, email, atau no. HP --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->user->name }} ({{ $member->member_code }}) - {{ $member->user->email }}{{ $member->user->phone ? ' - '.$member->user->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="text-muted">Ketik untuk mencari member</small>
                </div>

                <!--  CARI BUKU (kategori ikut di keyword, dropdown kategori dihapus) -->
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Cari Buku <span class="text-danger">*</span></label>
                    <select name="book_id" id="book_select" class="form-control @error('book_id') is-invalid @enderror" required>
                        <option value="">-- Ketik judul atau kategori buku --</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}"
                                    {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                {{ $book->title }} (Stok: {{ $book->available_stock }}) - {{ $book->category->name ?? 'Tanpa Kategori' }}
                            </option>
                        @endforeach
                    </select>
                    @error('book_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="text-muted" id="book_count">Total buku tersedia: {{ $books->count() }}</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Pinjam <span class="text-danger">*</span></label>
                    <input type="date" name="borrow_date" class="form-control @error('borrow_date') is-invalid @enderror" 
                           value="{{ old('borrow_date', date('Y-m-d')) }}" required>
                    @error('borrow_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                           value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required>
                    @error('due_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="text-muted">Masa pinjam standar 7 hari</small>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Catatan:</strong> Member hanya bisa meminjam maksimal 3 buku sekaligus dan tidak memiliki denda.
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Pinjam
                </button>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#member_select', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: '-- Ketik nama, kode, email, atau no. HP --',
        });

        new TomSelect('#book_select', {
            create: false,
            sortField: { field: 'text', direction: 'asc' },
            placeholder: '-- Ketik judul atau kategori buku --',
        });
    });
</script>
@endpush
@endsection