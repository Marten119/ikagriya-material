@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="GET" class="d-flex gap-2">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau username" style="min-width: 240px">
        <button class="btn btn-outline-primary" type="submit">Cari</button>
    </form>
    <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pengguna</a>
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Nama</th><th>Username</th><th>Email</th><th>Peran</th><th>Lokasi cluster</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
            @forelse ($users as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->email ?? '-' }}</td>
                    <td>{{ $u->role }}</td>
                    <td>
                        @if ($u->isAdmin())
                            <span class="text-muted">Semua cluster</span>
                        @elseif ($u->clusters->isEmpty())
                            <span class="text-danger">Belum diatur</span>
                        @else
                            {{ $u->clusters->pluck('cluster_name')->join(', ') }}
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $u->status === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $u->status }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pengguna ditemukan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection
