@extends('layouts.app')

@section('title', 'Manajemen Member')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Daftar Member</h5>
        <a href="{{ route('admin.members.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Member
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($members->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kode Member</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. Telepon</th>
                            <th>Tgl Daftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($members as $index => $member)
                        <tr>
                            <td>{{ $members->firstItem() + $index }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $member->member_code }}</span>
                            </td>
                            <td>{{ $member->user->name }}</td>
                            <td>{{ $member->user->email }}</td>
                            <td>{{ $member->user->phone ?? '-' }}</td>
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
                                    @csrf
                                    @method('PUT')
                                    @if($member->status == 'active')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Blokir member ini?')">
                                            <i class="fas fa-ban me-1"></i> Blokir
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Aktifkan member ini?')">
                                            <i class="fas fa-check me-1"></i> Aktifkan
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ✅ PAGINATION DENGAN STYLE BOOTSTRAP YANG LEBIH BAIK --}}
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Menampilkan <strong>{{ $members->firstItem() }}</strong> - <strong>{{ $members->lastItem() }}</strong> 
                    dari <strong>{{ $members->total() }}</strong> member
                </div>
                <div>
                    @if ($members->hasPages())
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                {{-- Previous Page Link --}}
                                @if ($members->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $members->previousPageUrl() }}" rel="prev">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                @endif
            
                                {{-- Pagination Elements --}}
                                @foreach ($members->links()->elements as $element)
                                    {{-- "Three Dots" Separator --}}
                                    @if (is_string($element))
                                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                                    @endif
            
                                    {{-- Array Of Links --}}
                                    @if (is_array($element))
                                        @foreach ($element as $page => $url)
                                            @if ($page == $members->currentPage())
                                                <li class="page-item active" aria-current="page">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
            
                                {{-- Next Page Link --}}
                                @if ($members->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $members->nextPageUrl() }}" rel="next">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">Belum ada data member.</p>
                <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Member
                </a>
            </div>
        @endif
    </div>
</div>
@endsection