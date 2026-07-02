@extends('layouts.app')

@section('title', 'Denda')

@section('content')
<div class="card">
    <div class="card-header bg-danger text-white">
        <h5>Daftar Denda</h5>
    </div>
    <div class="card-body">
        @php
            $totalUnpaid = $penalties->where('status', 'unpaid')->sum('fine_amount');
        @endphp
        
        @if($totalUnpaid > 0)
            <div class="alert alert-warning">
                <h6>Total Denda Belum Dibayar: <strong>Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</strong></h6>
            </div>
        @endif

        @if($penalties->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Buku</th>
                            <th>Terlambat</th>
                            <th>Denda</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penalties as $penalty)
                        <tr>
                            <td>{{ $penalty->member->user->name ?? '-' }}</td>
                            <td>{{ $penalty->transaction->book->title ?? '-' }}</td>
                            <td class="text-danger">{{ $penalty->days_late }} hari</td>
                            <td class="text-danger">Rp {{ number_format($penalty->fine_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($penalty->status == 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-danger">Belum Dibayar</span>
                                @endif
                            </td>
                            <td>
                                @if($penalty->status == 'unpaid')
                                    <form action="{{ route('admin.penalties.pay', $penalty) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bayar denda?')">Bayar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $penalties->links() }}
        @else
            <p class="text-muted">Tidak ada denda</p>
        @endif
    </div>
</div>
@endsection