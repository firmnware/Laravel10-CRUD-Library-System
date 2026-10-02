@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Daftar Transaksi</h5>
        <a href="{{ route('admin.transactions.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i> Pinjam Buku
        </a>
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

        <!--  FORM SEARCH & FILTER -->
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari kode, nama, email, no. HP, judul buku..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-control">
                        <option value="">-- Semua Status --</option>
                        <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Terlambat</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    @if(request('search') || request('status'))
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Menampilkan <strong>{{ $transactions->total() }}</strong> transaksi
                            </span>
                            @if(request('search'))
                                <span class="badge bg-info">Pencarian: "{{ request('search') }}"</span>
                            @endif
                            @if(request('status') == 'borrowed')
                                <span class="badge bg-primary">Status: Dipinjam</span>
                            @elseif(request('status') == 'overdue')
                                <span class="badge bg-danger">Status: Terlambat</span>
                            @elseif(request('status') == 'returned')
                                <span class="badge bg-success">Status: Dikembalikan</span>
                            @endif
                            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-times me-1"></i> Reset Filter
                            </a>
                        </div>
                    @else
                        <span class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Menampilkan <strong>{{ $transactions->total() }}</strong> transaksi
                        </span>
                    @endif
                </div>
            </div>
        </form>

        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Member</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $trx)
                        @php
                            $days = $trx->remaining_days;
                            $hasFine = $trx->has_fine;
                            $fineAmount = $trx->current_fine;
                            $penalty = $trx->penalty;
                            $penaltyStatus = $penalty ? $penalty->status : null;
                            $penaltyPaidDate = $penalty ? $penalty->paid_date : null;
                            $isReturned = $trx->status === 'returned';
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $trx->transaction_code }}</span>
                            </td>
                            <td>{{ $trx->member->user->name ?? '-' }}</td>
                            <td>{{ $trx->book->title ?? '-' }}</td>
                            <td>{{ $trx->borrow_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                {{ $trx->due_date?->format('d/m/Y') ?? '-' }}
                                @if($trx->status == 'borrowed' && $trx->due_date)
                                    @if($days < 0)
                                        <span class="badge bg-danger ms-1">Terlambat!</span>
                                    @elseif($days == 0)
                                        <span class="badge bg-warning ms-1">Hari Ini!</span>
                                    @elseif($days <= 2)
                                        <span class="badge bg-warning ms-1">Segera!</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($isReturned)
                                    <span class="text-muted">-</span>
                                @else
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
                                @endif
                            </td>
                            <td>
                                @if($isReturned)
                                    <span class="badge bg-success">Dikembalikan</span>
                                    @if($trx->return_date)
                                        <div class="small text-muted mt-1">
                                            {{ $trx->return_date->format('d/m/Y') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-{{ $trx->status_color }}">
                                        {{ $trx->status_text }}
                                    </span>
                                    @if($trx->warning_message)
                                        <div class="small text-danger mt-1">
                                            {{ $trx->warning_message }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{--  TAMPILKAN STATUS DENDA --}}
                                @if($penalty)
                                    @if($penalty->status == 'paid')
                                        <span class="text-success fw-bold">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Lunas
                                        </span>
                                        <div class="small text-muted">
                                            Rp {{ number_format($penalty->fine_amount, 0, ',', '.') }}
                                            <br>Tgl: {{ $penalty->paid_date?->format('d/m/Y') ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-danger fw-bold">
                                            <i class="fas fa-exclamation-circle me-1"></i>
                                            Belum Bayar
                                        </span>
                                        <div class="small text-danger">
                                            Rp {{ number_format($penalty->fine_amount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                @elseif($hasFine && !$isReturned)
                                    <span class="text-danger fw-bold">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Denda {{ number_format($fineAmount, 0, ',', '.') }}
                                    </span>
                                    <div class="small text-warning">
                                        Belum dicatat
                                    </div>
                                @elseif($isReturned)
                                    <span class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Tidak ada denda
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(!$isReturned)
                                    <form action="{{ route('admin.transactions.return', $trx) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Kembalikan buku ini?')">
                                            <i class="fas fa-undo me-1"></i> Kembalikan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Menampilkan <strong>{{ $transactions->firstItem() }}</strong> - <strong>{{ $transactions->lastItem() }}</strong>
                    dari <strong>{{ $transactions->total() }}</strong> transaksi
                </div>
                <div>
                    {{ $transactions->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada transaksi yang ditemukan</p>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-primary">
                        <i class="fas fa-undo me-1"></i> Lihat Semua Transaksi
                    </a>
                @else
                    <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Pinjam Buku Sekarang
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection