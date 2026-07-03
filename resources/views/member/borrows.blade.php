@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i> Riwayat Peminjaman</h5>
    </div>
    <div class="card-body">
        <!-- Statistik Ringkas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center py-2">
                        <h6>Total Pinjaman</h6>
                        <h4>{{ $borrows->total() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center py-2">
                        <h6>Sedang Dipinjam</h6>
                        <h4>{{ $borrows->where('status', 'borrowed')->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h6>Sudah Dikembalikan</h6>
                        <h4>{{ $borrows->where('status', 'returned')->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h6>Terlambat</h6>
                        <h4>{{ $borrows->filter(function($item) { 
                            return $item->status == 'borrowed' && $item->remaining_days < 0; 
                        })->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        @if($borrows->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Judul Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Sisa Hari</th>
                            <th>Status</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrows as $borrow)
                        @php
                            $hasFine = $borrow->has_fine;
                            $fineAmount = $borrow->current_fine;
                            $penalty = $borrow->penalty;
                            $penaltyStatus = $penalty ? $penalty->status : null;
                            $penaltyPaidDate = $penalty ? $penalty->paid_date : null;
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $borrow->transaction_code }}</span>
                            </td>
                            <td>
                                <strong>{{ $borrow->book->title ?? '-' }}</strong>
                            </td>
                            <td>{{ $borrow->borrow_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                {{ $borrow->due_date?->format('d/m/Y') ?? '-' }}
                                @if($borrow->status == 'borrowed' && $borrow->due_date)
                                    @php $days = $borrow->remaining_days; @endphp
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
                                @if($borrow->status == 'returned')
                                    <span class="text-muted">-</span>
                                @else
                                    @php $days = $borrow->remaining_days; @endphp
                                    @if($days < 0)
                                        <span class="text-danger fw-bold">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ abs($days) }} hari terlambat
                                        </span>
                                    @elseif($days == 0)
                                        <span class="text-warning fw-bold">
                                            <i class="fas fa-clock me-1"></i>
                                            Jatuh tempo hari ini!
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
                                @if($borrow->status == 'borrowed')
                                    <span class="badge bg-{{ $borrow->status_color }}">
                                        {{ $borrow->status_text }}
                                    </span>
                                    @if($borrow->warning_message)
                                        <div class="small text-danger mt-1">
                                            {{ $borrow->warning_message }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-success">Dikembalikan</span>
                                    @if($borrow->return_date)
                                        <div class="small text-muted mt-1">
                                            {{ $borrow->return_date->format('d/m/Y') }}
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
                {{ $borrows->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada riwayat peminjaman</p>
            </div>
        @endif
    </div>
</div>
@endsection