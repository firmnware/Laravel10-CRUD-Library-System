@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5>Daftar Transaksi</h5>
        <a href="{{ route('admin.transactions.create') }}" class="btn btn-light btn-sm">Pinjam Buku</a>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $trx)
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
                            <td>
                                @if($trx->status == 'borrowed')
                                    <form action="{{ route('admin.transactions.return', $trx) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Kembalikan?')">Kembalikan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $transactions->links() }}
        @else
            <p class="text-muted">Belum ada transaksi</p>
        @endif
    </div>
</div>
@endsection