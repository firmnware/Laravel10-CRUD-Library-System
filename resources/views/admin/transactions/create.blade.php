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
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Pilih Member <span class="text-danger">*</span></label>
                    <select name="member_id" class="form-control @error('member_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Member --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->user->name }} ({{ $member->member_code }})
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!--  KATEGORI BUKU -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Kategori Buku</label>
                    <select id="category_filter" class="form-control">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Pilih kategori untuk menyaring daftar buku</small>
                </div>

                <!--  DAFTAR BUKU (Terintegrasi dengan Kategori) -->
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Pilih Buku <span class="text-danger">*</span></label>
                    <select name="book_id" id="book_list" class="form-control @error('book_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Buku --</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}" data-category="{{ $book->category_id }}" 
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categoryFilter = document.getElementById('category_filter');
        const bookList = document.getElementById('book_list');
        const bookCount = document.getElementById('book_count');
        const allBooks = bookList.querySelectorAll('option');

        //  Fungsi filter buku berdasarkan kategori
        function filterBooksByCategory(categoryId) {
            let visibleCount = 0;
            
            // Reset semua option
            allBooks.forEach(option => {
                // Skip option pertama (placeholder)
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }
                
                const bookCategory = option.getAttribute('data-category');
                
                if (categoryId === '' || categoryId === null || bookCategory == categoryId) {
                    option.style.display = 'block';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });

            // Update jumlah buku
            bookCount.textContent = `Total buku tersedia: ${visibleCount}`;

            // Reset pilihan jika buku yang dipilih tidak terlihat
            const selectedOption = bookList.options[bookList.selectedIndex];
            if (selectedOption && selectedOption.value !== '' && selectedOption.style.display === 'none') {
                bookList.value = '';
            }
        }

        //  Event listener untuk perubahan kategori
        categoryFilter.addEventListener('change', function() {
            const categoryId = this.value;
            filterBooksByCategory(categoryId);
        });

        //  Cek old value untuk book_id dan set kategori yang sesuai
        const oldBookId = "{{ old('book_id') }}";
        if (oldBookId) {
            // Cari option yang memiliki value = oldBookId
            const selectedOption = bookList.querySelector(`option[value="${oldBookId}"]`);
            if (selectedOption) {
                const categoryId = selectedOption.getAttribute('data-category');
                if (categoryId) {
                    // Set kategori filter sesuai dengan buku yang dipilih
                    categoryFilter.value = categoryId;
                    filterBooksByCategory(categoryId);
                    
                    // Set selected option
                    bookList.value = oldBookId;
                }
            }
        } else {
            //  Jika tidak ada old value, tampilkan semua buku
            filterBooksByCategory('');
        }
    });
</script>
@endpush
@endsection