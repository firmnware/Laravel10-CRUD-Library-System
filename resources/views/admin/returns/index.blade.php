@extends('layouts.app')

@section('title', 'Pengembalian')

@section('content')
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-undo me-2"></i> Pengembalian Buku</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <p class="text-muted">
            <i class="fas fa-info-circle me-2"></i>
            Masukkan kode peminjaman (contoh: PJM-20260101-AB12CD), lalu isi tanggal pengembalian di halaman berikutnya.
        </p>

        <form action="{{ route('admin.returns.search') }}" method="POST" class="row g-2">
            @csrf
            <div class="col-md-8">
                <input type="text" name="loan_code" class="form-control form-control-lg @error('loan_code') is-invalid @enderror"
                       placeholder="Kode peminjaman..." value="{{ old('loan_code') }}" required autofocus>
                @error('loan_code')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-search me-1"></i> Cari Peminjaman
                </button>
            </div>
        </form>

        <div class="mt-3">
            <a href="{{ route('admin.loans.index') }}" class="btn btn-link">
                <i class="fas fa-list me-1"></i> Lihat daftar peminjaman
            </a>
        </div>
    </div>
</div>
@endsection
