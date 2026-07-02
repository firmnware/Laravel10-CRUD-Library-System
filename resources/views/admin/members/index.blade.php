@extends('layouts.app')

@section('title', 'Manajemen Member')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5>Daftar Member</h5>
        <a href="{{ route('admin.members.create') }}" class="btn btn-light btn-sm">Tambah Member</a>
    </div>
    <div class="card-body">
        @if($members->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tgl Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $member)
                        <tr>
                            <td>{{ $member->member_code }}</td>
                            <td>{{ $member->user->name }}</td>
                            <td>{{ $member->user->email }}</td>
                            <td>{{ $member->join_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($member->status == 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Diblokir</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.members.toggle-status', $member) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <button type="submit" class="btn btn-{{ $member->status == 'active' ? 'danger' : 'success' }} btn-sm">
                                        {{ $member->status == 'active' ? 'Blokir' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $members->links() }}
        @else
            <p class="text-muted">Belum ada member</p>
        @endif
    </div>
</div>
@endsection