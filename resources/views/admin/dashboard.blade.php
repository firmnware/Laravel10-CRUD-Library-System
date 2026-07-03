@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h3 class="mb-4">Dashboard Admin</h3>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Buku</h5>
                <h2 class="mb-0">{{ $totalBooks ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Total Member</h5>
                <h2 class="mb-0">{{ $totalMembers ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5>Dipinjam</h5>
                <h2 class="mb-0">{{ $activeBorrows ?? 0 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>Total Denda</h5>
                <h2 class="mb-0">Rp {{ number_format($totalPenalties ?? 0, 0, ',', '.') }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Transaksi Terbaru -->
<div class="card">
    <div class="card-header">
        <h5>Transaksi Terbaru</h5>
    </div>
    <div class="card-body">
        @if(isset($recentTransactions) && $recentTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Member</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $trx)
                        <tr>
                            <td>{{ $trx->transaction_code }}</td>
                            <td>{{ $trx->member->user->name ?? '-' }}</td>
                            <td>{{ $trx->book->title ?? '-' }}</td>
                            <td>{{ $trx->borrow_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $trx->due_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($trx->status == 'borrowed')
                                    <span class="badge bg-warning">Dipinjam</span>
                                @else
                                    <span class="badge bg-success">Dikembalikan</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(isset($recentTransactions) && method_exists($recentTransactions, 'links'))
                <div class="d-flex justify-content-center">
                    {{ $recentTransactions->links() }}
                </div>
            @endif
        @else
            <p class="text-muted">Belum ada transaksi</p>
        @endif
    </div>
</div>
@endsection