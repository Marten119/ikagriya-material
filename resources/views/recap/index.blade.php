@extends('layouts.app')
@section('title', 'Rekapitulasi')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger py-2">@foreach ($errors->all() as $err) <div>{{ $err }}</div> @endforeach</div>
@endif

<form method="GET" class="panel panel-body mb-3">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="date_from">Dari tanggal</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="date_to">Sampai tanggal</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="cluster_id">Lokasi cluster</label>
            <select id="cluster_id" name="cluster_id" class="form-select">
                <option value="">Semua cluster</option>
                @foreach ($clusters as $c)
                    <option value="{{ $c->id }}" @selected((string) request('cluster_id') === (string) $c->id)>{{ $c->cluster_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="category_id">Kategori material</label>
            <select id="category_id" name="category_id" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->category_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="material_id">Nama material</label>
            <select id="material_id" name="material_id" class="form-select">
                <option value="">Semua material</option>
                @foreach ($materials as $catName => $list)
                    <optgroup label="{{ $catName }}">
                        @foreach ($list as $m)
                            <option value="{{ $m->id }}" @selected((string) request('material_id') === (string) $m->id)>{{ $m->material_code }} - {{ $m->material_name }}{{ $m->status === 'aktif' ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="unit_id">Satuan</label>
            <select id="unit_id" name="unit_id" class="form-select">
                <option value="">Semua satuan</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}" @selected((string) request('unit_id') === (string) $u->id)>{{ $u->unit_code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="group_by">Kelompokkan</label>
            <select id="group_by" name="group_by" class="form-select">
                @foreach (\App\Services\RecapService::GROUPS as $key => $label)
                    <option value="{{ $key }}" @selected($group === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3" id="period-wrap">
            <label class="form-label" for="period">Interval periode</label>
            <select id="period" name="period" class="form-select">
                @foreach (\App\Services\RecapService::PERIODS as $key => $label)
                    <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Terapkan</button>
        <a href="{{ route('recap.index') }}" class="btn btn-outline-secondary">Atur ulang</a>
    </div>
</form>

<div class="d-flex justify-content-between align-items-end mb-2">
    <div class="text-muted small">{{ $rows->total() }} baris hasil &middot; {{ $totalTransactions }} transaksi sesuai filter</div>
</div>

<div class="panel">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    @foreach ($columns as $col) <th>{{ $col }}</th> @endforeach
                    <th class="text-end">Transaksi</th>
                    <th class="text-end">Total volume masuk</th>
                    <th class="text-end">Pemakaian per unit*</th>
                    <th class="text-end">Pemakaian (m²)*</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row['dims'] as $cell) <td>{{ $cell }}</td> @endforeach
                    <td class="text-end">{{ $row['trx'] }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($row['volume'], 3) }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($row['per_unit'], 3) }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($row['m2'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ count($columns) + 4 }}" class="text-center text-muted py-4">Tidak ada data untuk filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $rows->links('pagination::bootstrap-5') }}</div>

<p class="text-muted small mt-3 mb-0">
    Volume selalu dijumlahkan per satuan; satuan yang berbeda tidak digabung.
    *Pemakaian per unit dan pemakaian (m²) adalah jumlah nilai yang tercatat pada transaksi, bukan hasil rumus perusahaan (rumus resmi belum ditetapkan).
</p>
@endsection

@push('scripts')
<script>
(function () {
    const group = document.getElementById('group_by');
    const wrap = document.getElementById('period-wrap');
    function sync() { wrap.style.display = group.value === 'period' ? '' : 'none'; }
    group.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
