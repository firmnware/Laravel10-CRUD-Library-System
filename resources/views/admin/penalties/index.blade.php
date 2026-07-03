@extends('layouts.app')

@section('title', 'Denda')

@section('content')
<div class="card">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i> Daftar Denda</h5>
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

        <!-- Statistik Denda -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center py-2">
                        <h6>Total Denda</h6>
                        <h4>Rp {{ number_format($totalUnpaid + $totalPaid, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center py-2">
                        <h6>Belum Dibayar</h6>
                        <h4>Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center py-2">
                        <h6>Sudah Dibayar</h6>
                        <h4>Rp {{ number_format($totalPaid, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        @if($totalUnpaid > 0)
            <div class="alert alert-warning">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle me-2"></i> 
                    Total Denda Belum Dibayar: <strong>Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</strong>
                </h5>
            </div>
        @endif

        @if($penalties->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kode Transaksi</th>
                            <th>Member</th>
                            <th>Judul Buku</th>
                            <th>Jatuh Tempo</th>
                            <th>Terlambat</th>
                            <th>Jumlah Denda</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penalties as $index => $penalty)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $penalty->transaction->transaction_code }}</span>
                            </td>
                            <td>{{ $penalty->transaction->member->user->name ?? '-' }}</td>
                            <td>{{ $penalty->transaction->book->title ?? '-' }}</td>
                            <td>
                                {{ Carbon\Carbon::parse($penalty->transaction->due_date)->format('d/m/Y') }}
                                @if(Carbon\Carbon::parse($penalty->transaction->due_date)->isPast() && $penalty->transaction->status == 'borrowed')
                                    <span class="badge bg-danger ms-1">Terlambat!</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-danger fw-bold">{{ $penalty->days_late }} hari</span>
                            </td>
                            <td>
                                <span class="text-danger fw-bold">Rp {{ number_format($penalty->fine_amount, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @if($penalty->status == 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                    <div class="small text-muted mt-1">
                                        Tgl: {{ $penalty->paid_date?->format('d/m/Y') ?? '-' }}
                                    </div>
                                @else
                                    <span class="badge bg-danger">Belum Dibayar</span>
                                @endif
                            </td>
                            <td>
                                @if($penalty->status == 'unpaid')
                                    {{--  ARAHKAN KE FORM PEMBAYARAN --}}
                                    <a href="{{ route('admin.penalties.pay.form', $penalty) }}" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-money-bill me-1"></i> Bayar
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="6" class="text-end">Total Denda:</th>
                            <th colspan="3">Rp {{ number_format($penalties->sum('fine_amount'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">Tidak ada denda</p>
            </div>
        @endif
    </div>
</div>
@endsection