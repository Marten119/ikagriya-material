@extends('layouts.app')
@section('title', 'Data Material')
@section('content')
<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
    <form method="GET" class="d-flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari kode atau nama" style="min-width: 220px">
        <select name="category_id" class="form-select" style="width: auto">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->category_name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select" style="width: auto">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <button class="btn btn-outline-primary" type="submit">Terapkan</button>
    </form>
    @if (auth()->user()->isAdmin())
        <a href="{{ route('materials.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Material</a>
    @endif
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Kode</th><th>Nama material</th><th>Kategori</th><th>Satuan standar</th><th>Status</th>@if (auth()->user()->isAdmin())<th class="text-end">Aksi</th>@endif</tr>
            </thead>
            <tbody>
            @forelse ($materials as $m)
                <tr>
                    <td>{{ $m->material_code }}</td>
                    <td>{{ $m->material_name }}</td>
                    <td>{{ $m->category->category_name }}</td>
                    <td>{{ $m->defaultUnit?->unit_code ?? '-' }}</td>
                    <td><span class="badge {{ $m->status === 'aktif' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $m->status }}</span></td>
                    @if (auth()->user()->isAdmin())
                    <td class="text-end text-nowrap">
                        <a href="{{ route('materials.edit', $m) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="{{ route('materials.status', $m) }}" class="d-inline"
                              onsubmit="return confirm('{{ $m->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} material ini?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $m->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada material ditemukan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $materials->links('pagination::bootstrap-5') }}</div>
@endsection
