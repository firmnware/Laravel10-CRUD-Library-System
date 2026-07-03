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
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
                                @if($trx->status == 'returned')
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
                                @if($trx->status == 'borrowed')
                                    <span class="badge bg-{{ $trx->status_color }}">
                                        {{ $trx->status_text }}
                                    </span>
                                    @if($trx->warning_message)
                                        <div class="small text-danger mt-1">
                                            {{ $trx->warning_message }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-success">Dikembalikan</span>
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
                                @elseif($hasFine)
                                    <span class="text-danger fw-bold">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Denda {{ number_format($fineAmount, 0, ',', '.') }}
                                    </span>
                                    <div class="small text-warning">
                                        Belum dicatat
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($trx->status == 'borrowed')
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
            <div class="d-flex justify-content-center mt-3">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada transaksi peminjaman.</p>
            </div>
        @endif
    </div>
</div>
@endsection