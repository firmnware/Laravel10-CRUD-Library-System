@extends('layouts.app')

@section('title', 'Denda Saya')

@section('content')
<div class="card">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0"><i class="fas fa-money-bill me-2"></i> Denda Saya</h5>
    </div>
    <div class="card-body">
        @php
            $totalUnpaid = $penalties->where('status', 'unpaid')->sum('fine_amount');
            $totalPaid = $penalties->where('status', 'paid')->sum('fine_amount');
        @endphp

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
                <p class="mb-0">Silahkan segera membayar denda di perpustakaan untuk menghindari pemblokiran akun.</p>
            </div>
        @endif

        @if($penalties->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kode Transaksi</th>
                            <th>Judul Buku</th>
                            <th>Tanggal Jatuh Tempo</th>
                            <th>Tanggal Kembali</th>
                            <th>Terlambat</th>
                            <th>Jumlah Denda</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penalties as $index => $penalty)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $penalty->transaction->transaction_code }}</td>
                            <td>{{ $penalty->transaction->book->title ?? '-' }}</td>
                            <td>{{ Carbon\Carbon::parse($penalty->transaction->due_date)->format('d/m/Y') }}</td>
                            <td>
                                @if($penalty->transaction->return_date)
                                    {{ Carbon\Carbon::parse($penalty->transaction->return_date)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Belum Dikembalikan</span>
                                @endif
                            </td>
                            <td class="text-danger fw-bold">{{ $penalty->days_late }} hari</td>
                            <td class="text-danger fw-bold">Rp {{ number_format($penalty->fine_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($penalty->status == 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                    @if($penalty->paid_date)
                                        <div class="small text-muted mt-1">
                                            Tgl: {{ Carbon\Carbon::parse($penalty->paid_date)->format('d/m/Y') }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-danger">Belum Dibayar</span>
                                    @if($penalty->transaction->status == 'borrowed')
                                        <div class="small text-warning mt-1">
                                            <i class="fas fa-clock me-1"></i>
                                            Buku masih dipinjam
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th colspan="6" class="text-end">Total Denda:</th>
                            <th colspan="2">Rp {{ number_format($penalties->sum('fine_amount'), 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-muted">Selamat! Anda tidak memiliki denda.</p>
            </div>
        @endif
    </div>
</div>
@endsection