@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h3 class="mb-4">Dashboard Member</h3>
        <h5>Selamat datang, {{ auth()->user()->name }}!</h5>
        <hr>
    </div>
</div>

<!--  Peringatan Real-Time -->
@if(isset($currentBorrows) && $currentBorrows->count() > 0)
    @php
        $overdueBooks = $currentBorrows->filter(function($item) {
            return $item->remaining_days < 0;
        });
        $nearDueBooks = $currentBorrows->filter(function($item) {
            return $item->remaining_days >= 0 && $item->remaining_days <= 2;
        });
    @endphp

    @if($overdueBooks->count() > 0)
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                Peringatan! Buku Terlambat Dikembalikan
            </h5>
            <ul class="mb-0">
                @foreach($overdueBooks as $book)
                    <li>
                        <strong>{{ $book->book->title }}</strong> 
                        - Terlambat {{ abs($book->remaining_days) }} hari
                        @php
                            $fine = abs($book->remaining_days) * 2000;
                        @endphp
                        <span class="text-danger">(Denda Rp {{ number_format($fine, 0, ',', '.') }})</span>
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($nearDueBooks->count() > 0 && $overdueBooks->count() == 0)
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <h5 class="alert-heading">
                <i class="fas fa-clock me-2"></i> 
                Peringatan! Buku Akan Jatuh Tempo
            </h5>
            <ul class="mb-0">
                @foreach($nearDueBooks as $book)
                    <li>
                        <strong>{{ $book->book->title }}</strong> 
                        - Jatuh tempo dalam {{ $book->remaining_days }} hari lagi
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endif

<!-- Statistik Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>Kode Member</h5>
                <h4>{{ $member->member_code ?? '-' }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5>Buku Dipinjam</h5>
                <h2>{{ $activeBorrows ?? 0 }}</h2>
                <small>dari maksimal 3 buku</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>Total Denda</h5>
                <h4>Rp {{ number_format($unpaidPenalty ?? 0, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Buku yang Sedang Dipinjam -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Buku yang Sedang Dipinjam</h5>
    </div>
    <div class="card-body">
        @if(isset($currentBorrows) && $currentBorrows->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentBorrows as $borrow)
                        <tr>
                            <td>{{ $borrow->book->title ?? '-' }}</td>
                            <td>{{ $borrow->borrow_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="{{ $borrow->due_date && $borrow->due_date->isPast() ? 'text-danger fw-bold' : '' }}">
                                {{ $borrow->due_date?->format('d/m/Y') ?? '-' }}
                                @if($borrow->due_date && $borrow->due_date->isPast()) 
                                    <span class="badge bg-danger">Terlambat!</span>
                                @elseif($borrow->remaining_days <= 2)
                                    <span class="badge bg-warning">Segera!</span>
                                @endif
                            </td>
                            <td>
                                @php $days = $borrow->remaining_days; @endphp
                                @if($days < 0)
                                    <span class="text-danger fw-bold">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        {{ abs($days) }} hari terlambat
                                    </span>
                                @elseif($days == 0)
                                    <span class="text-warning fw-bold">
                                        <i class="fas fa-clock me-1"></i>
                                        Hari ini!
                                    </span>
                                @elseif($days <= 2)
                                    <span class="text-warning">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        {{ $days }} hari lagi
                                    </span>
                                @else
                                    <span class="text-success">
                                        <i class="fas fa-hourglass-start me-1"></i>
                                        {{ $days }} hari
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $borrow->status_color }}">
                                    {{ $borrow->status_text }}
                                </span>
                                @if($borrow->warning_message)
                                    <div class="small text-danger mt-1">
                                        {{ $borrow->warning_message }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted">Tidak ada buku yang sedang dipinjam</p>
        @endif
    </div>
</div>

<!-- Tombol Cepat -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <a href="{{ route('member.books.index') }}" class="text-white text-decoration-none">
                    <i class="fas fa-book fa-3x mb-2 d-block"></i>
                    <h5>Cari Buku</h5>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <a href="{{ route('member.borrows') }}" class="text-white text-decoration-none">
                    <i class="fas fa-history fa-3x mb-2 d-block"></i>
                    <h5>Riwayat Pinjam</h5>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <a href="{{ route('member.penalties') }}" class="text-white text-decoration-none">
                    <i class="fas fa-money-bill fa-3x mb-2 d-block"></i>
                    <h5>Cek Denda</h5>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Peminjaman -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Riwayat Peminjaman Terakhir</h5>
    </div>
    <div class="card-body">
        @if(isset($borrowHistory) && $borrowHistory->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowHistory as $borrow)
                        <tr>
                            <td>{{ $borrow->transaction_code }}</td>
                            <td>{{ $borrow->book->title ?? '-' }}</td>
                            <td>{{ $borrow->borrow_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $borrow->return_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($borrow->status == 'borrowed')
                                    <span class="badge bg-{{ $borrow->status_color }}">
                                        {{ $borrow->status_text }}
                                    </span>
                                @else
                                    <span class="badge bg-success">Dikembalikan</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end">
                <a href="{{ route('member.borrows') }}" class="btn btn-primary btn-sm">
                    Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        @else
            <p class="text-muted">Belum ada riwayat peminjaman</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto refresh setiap 60 detik untuk update status real-time
    setTimeout(function() {
        location.reload();
    }, 60000);
</script>
@endpush
@endsection