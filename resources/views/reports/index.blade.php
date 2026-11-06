@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger py-2">@foreach ($errors->all() as $err) <div>{{ $err }}</div> @endforeach</div>
@endif

<form method="GET" action="{{ route('reports.index') }}" class="panel panel-body mb-3" id="report-form">
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label" for="type">Jenis laporan</label>
            <select id="type" name="type" class="form-select">
                @foreach (\App\Services\ReportService::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(request('type', 'monthly') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 period-field" data-for="daily">
            <label class="form-label" for="date">Tanggal</label>
            <input type="date" id="date" name="date" value="{{ request('date', $defaults['date']) }}" class="form-control">
        </div>
        <div class="col-md-3 period-field" data-for="weekly">
            <label class="form-label" for="week">Minggu</label>
            <input type="week" id="week" name="week" value="{{ request('week', $defaults['week']) }}" placeholder="2026-W42" pattern="\d{4}-W\d{2}" class="form-control">
        </div>
        <div class="col-md-3 period-field" data-for="monthly">
            <label class="form-label" for="month">Bulan</label>
            <input type="month" id="month" name="month" value="{{ request('month', $defaults['month']) }}" placeholder="2026-10" pattern="\d{4}-\d{2}" class="form-control">
        </div>
        <div class="col-md-3 period-field" data-for="yearly">
            <label class="form-label" for="year">Tahun</label>
            <input type="number" id="year" name="year" min="2000" max="2100" value="{{ request('year', $defaults['year']) }}" class="form-control">
        </div>
        <div class="col-md-3 period-field" data-for="range">
            <label class="form-label" for="date_from">Dari tanggal</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from', $defaults['date_from']) }}" class="form-control">
        </div>
        <div class="col-md-3 period-field" data-for="range">
            <label class="form-label" for="date_to">Sampai tanggal</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to', $defaults['date_to']) }}" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label" for="layout">Susunan laporan</label>
            <select id="layout" name="layout" class="form-select">
                @foreach (\App\Services\ReportService::LAYOUTS as $key => $label)
                    <option value="{{ $key }}" @selected(request('layout', 'detail') === $key)>{{ $label }}</option>
                @endforeach
            </select>
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
            <label class="form-label" for="category_id">Kategori</label>
            <select id="category_id" name="category_id" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->category_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="material_id">Material</label>
            <select id="material_id" name="material_id" class="form-select">
                <option value="">Semua material</option>
                @foreach ($materials as $catName => $list)
                    <optgroup label="{{ $catName }}">
                        @foreach ($list as $m)
                            <option value="{{ $m->id }}" @selected((string) request('material_id') === (string) $m->id)>{{ $m->material_code }} - {{ $m->material_name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </div>
    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">Tampilkan laporan</button>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Atur ulang</a>
    </div>
</form>

@if ($report)
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('reports.print', request()->query()) }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-printer"></i> Cetak</a>
        <a href="{{ route('reports.excel', request()->query()) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-excel"></i> Excel</a>
        <a href="{{ route('reports.pdf', request()->query()) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
    </div>
    <div class="panel panel-body">
        @include('reports._style')
        @include('reports._document')
    </div>
@else
    <div class="text-muted">Pilih jenis laporan dan periode, lalu tekan Tampilkan laporan.</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const type = document.getElementById('type');
    const fields = document.querySelectorAll('.period-field');
    function sync() {
        fields.forEach(function (f) { f.style.display = f.dataset.for === type.value ? '' : 'none'; });
    }
    type.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
