@extends('layouts.app')
@section('title', 'Data Lokasi Cluster')
@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="GET" class="d-flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode atau nama" style="min-width: 220px">
        <select name="status" class="form-select" style="width: auto">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <button class="btn btn-outline-primary" type="submit">Terapkan</button>
    </form>
    @if (auth()->user()->isAdmin())
        <a href="{{ route('clusters.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Cluster</a>
    @endif
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Nama cluster</th><th>Keterangan</th><th class="text-end">Transaksi</th><th>Status</th>@if (auth()->user()->isAdmin())<th class="text-end">Aksi</th>@endif</tr>
            </thead>
            <tbody>
            @forelse ($clusters as $c)
                <tr>
                    <td>{{ $c->cluster_code }}</td>
                    <td>{{ $c->cluster_name }}</td>
                    <td class="text-muted">{{ $c->description ?? '-' }}</td>
                    <td class="text-end">{{ $c->receipts_count }}</td>
                    <td><span class="badge {{ $c->status === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $c->status }}</span></td>
                    @if (auth()->user()->isAdmin())
                    <td class="text-end text-nowrap">
                        <a href="{{ route('clusters.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="{{ route('clusters.status', $c) }}" class="d-inline"
                              onsubmit="return confirm('{{ $c->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} cluster ini?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $c->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data lokasi cluster.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $clusters->links('pagination::bootstrap-5') }}</div>
@endsection
