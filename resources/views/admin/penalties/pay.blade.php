@extends('layouts.app')

@section('title', 'Bayar Denda')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white text-center">
                <h5 class="mb-0">
                    <i class="fas fa-money-bill me-2"></i> 
                    Konfirmasi Pembayaran Denda
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Anda akan membayar denda untuk transaksi berikut:
                </div>

                <table class="table table-bordered">
                    <tr>
                        <th width="150">Kode Transaksi</th>
                        <td><span class="badge bg-secondary">{{ $penalty->transaction->transaction_code }}</span></td>
                    </tr>
                    <tr>
                        <th>Member</th>
                        <td>{{ $penalty->transaction->member->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Judul Buku</th>
                        <td>{{ $penalty->transaction->book->title ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jatuh Tempo</th>
                        <td>{{ $penalty->transaction->due_date?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Kembali</th>
                        <td>{{ $penalty->transaction->return_date?->format('d/m/Y') ?? 'Belum Dikembalikan' }}</td>
                    </tr>
                    <tr>
                        <th>Terlambat</th>
                        <td class="text-danger fw-bold">{{ $penalty->days_late }} hari</td>
                    </tr>
                    <tr>
                        <th>Jumlah Denda</th>
                        <td class="text-danger fw-bold h4">
                            Rp {{ number_format($penalty->fine_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Perhatian:</strong> Pembayaran denda ini akan dicatat sebagai lunas.
                </div>

                <form action="{{ route('admin.penalties.pay', $penalty) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Konfirmasi pembayaran denda ini?')">
                            <i class="fas fa-check-circle me-2"></i> 
                            Konfirmasi Pembayaran
                        </button>
                        <a href="{{ route('admin.penalties.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> 
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection