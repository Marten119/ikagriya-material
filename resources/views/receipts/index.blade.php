@extends('layouts.app')
@section('title', 'Input Material Masuk')
@section('content')
<div class="panel mb-4">
    <div class="panel-body">
        <h2 class="h6 fw-semibold mb-3">Catat material masuk</h2>
        @include('receipts._form')
    </div>
</div>

<h2 class="h6 fw-semibold mb-2">Data transaksi</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="No. transaksi, kode atau nama material">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="Dari tanggal">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="Sampai tanggal">
    </div>
    <div class="col-md-2">
        <select name="cluster_id" class="form-select">
            <option value="">Semua cluster</option>
            @foreach ($filterClusters as $c)
                <option value="{{ $c->id }}" @selected((string) request('cluster_id') === (string) $c->id)>{{ $c->cluster_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <select name="category_id" class="form-select">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->category_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-1 d-grid">
        <button class="btn btn-outline-primary" type="submit">Cari</button>
    </div>
</form>

<div class="panel">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>No. transaksi</th><th>Tanggal</th><th>Cluster</th><th>Kategori</th><th>Material</th>
                    <th class="text-end">Volume</th><th>Satuan</th><th class="text-end">Pemakaian/unit</th><th class="text-end">Pemakaian (m²)</th>
                    <th>Dicatat oleh</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($receipts as $r)
                <tr>
                    <td class="text-nowrap">{{ $r->transaction_number }}</td>
                    <td class="text-nowrap">{{ $r->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $r->cluster->cluster_name }}</td>
                    <td>{{ $r->material->category->category_name }}</td>
                    <td>{{ $r->material->material_name }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($r->volume, 3) }}</td>
                    <td>{{ $r->unit->unit_code }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($r->usage_per_unit, 3) }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($r->usage_m2, 2) }}</td>
                    <td>{{ $r->creator->name }}</td>
                    <td class="text-end text-nowrap">
                        @can('update', $r)
                            <a href="{{ route('receipts.edit', $r) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        @endcan
                        @can('delete', $r)
                            <form method="POST" action="{{ route('receipts.destroy', $r) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus transaksi {{ $r->transaction_number }}? Tindakan ini dicatat pada log aktivitas.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted py-4">Belum ada transaksi yang sesuai.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $receipts->links('pagination::bootstrap-5') }}</div>
@endsection
