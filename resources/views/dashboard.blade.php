@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger py-2">@foreach ($errors->all() as $err) <div>{{ $err }}</div> @endforeach</div>
@endif

<form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-md-3">
        <label class="form-label small mb-1" for="date_from">Dari tanggal</label>
        <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label small mb-1" for="date_to">Sampai tanggal</label>
        <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label small mb-1" for="cluster_id">Lokasi cluster</label>
        <select id="cluster_id" name="cluster_id" class="form-select">
            <option value="">Semua cluster</option>
            @foreach ($clusters as $c)
                <option value="{{ $c->id }}" @selected((string) $filters['cluster_id'] === (string) $c->id)>{{ $c->cluster_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Atur ulang</a>
    </div>
</form>

<div class="panel mb-3">
    <div class="row g-0 text-center">
        <div class="col-4 py-3 border-end">
            <div class="fs-4 fw-semibold">{{ $summary['transactions'] }}</div>
            <div class="text-muted small">Transaksi material masuk</div>
        </div>
        <div class="col-4 py-3 border-end">
            <div class="fs-4 fw-semibold">{{ $summary['categories'] }}</div>
            <div class="text-muted small">Kategori tercatat</div>
        </div>
        <div class="col-4 py-3">
            <div class="fs-4 fw-semibold">{{ $summary['clusters'] }}</div>
            <div class="text-muted small">Cluster bertransaksi</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="panel panel-body h-100">
            <h2 class="h6 fw-semibold">Jumlah transaksi per {{ $granularity === 'day' ? 'hari' : 'bulan' }}</h2>
            <div style="position: relative; height: 260px;">
                <canvas id="timeline-chart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel h-100">
            <div class="panel-body pb-2"><h2 class="h6 fw-semibold mb-0">Rekap per kategori</h2></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Kategori</th><th>Satuan</th><th class="text-end">Trx</th><th class="text-end">Total volume</th></tr></thead>
                    <tbody>
                    @forelse ($categoryRecap as $row)
                        <tr>
                            <td>{{ $row['dims'][0] }}</td>
                            <td>{{ $row['dims'][1] }}</td>
                            <td class="text-end">{{ $row['trx'] }}</td>
                            <td class="text-end">{{ \App\Support\Fmt::num($row['volume'], 3) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada transaksi pada periode ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-body pb-2 d-flex justify-content-between align-items-center">
        <h2 class="h6 fw-semibold mb-0">Transaksi terbaru</h2>
        <a href="{{ route('receipts.index') }}" class="small">Lihat semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>No. transaksi</th><th>Tanggal</th><th>Cluster</th><th>Material</th><th class="text-end">Volume</th><th>Satuan</th></tr></thead>
            <tbody>
            @forelse ($recent as $r)
                <tr>
                    <td class="text-nowrap">{{ $r->transaction_number }}</td>
                    <td class="text-nowrap">{{ $r->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $r->cluster->cluster_name }}</td>
                    <td>{{ $r->material->material_name }}</td>
                    <td class="text-end">{{ \App\Support\Fmt::num($r->volume, 3) }}</td>
                    <td>{{ $r->unit->unit_code }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada transaksi pada periode ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    const el = document.getElementById('timeline-chart');
    if (!el || typeof Chart === 'undefined') return;
    new Chart(el, {
        type: 'bar',
        data: {
            labels: @json($timeline['labels']),
            datasets: [{ label: 'Transaksi', data: @json($timeline['data']), backgroundColor: '#1f3a5f' }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
})();
</script>
@endpush
