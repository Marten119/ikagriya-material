<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Cluster;
use App\Models\Material;
use App\Models\MaterialReceipt;
use Carbon\Carbon;

class ReportService
{
    public const COMPANY = 'PT. Ikagriya Darmapersada';

    public const TYPES = [
        'daily' => 'Harian',
        'weekly' => 'Mingguan',
        'monthly' => 'Bulanan',
        'yearly' => 'Tahunan',
        'range' => 'Rentang Tanggal',
    ];

    public const LAYOUTS = [
        'detail' => 'Rincian transaksi',
        'material' => 'Per material',
        'category' => 'Per kategori',
        'cluster' => 'Per lokasi cluster',
    ];

    public function __construct(private RecapService $recap)
    {
    }

    /**
     * Susun laporan. Hasilnya berupa array murni yang dipakai bersama oleh
     * pratinjau, tampilan cetak, Excel, dan PDF. Sel angka berbentuk [nilai, jumlahDesimal].
     * $limit membatasi baris rincian (untuk pratinjau dan PDF).
     */
    public function build(array $p, ?int $limit = null): array
    {
        [$from, $to, $periodLabel] = $this->resolvePeriod($p);

        $filters = [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'cluster_id' => $p['cluster_id'] ?? null,
            'category_id' => $p['category_id'] ?? null,
            'material_id' => $p['material_id'] ?? null,
        ];

        $layout = $p['layout'] ?? 'detail';
        $report = [
            'company' => self::COMPANY,
            'title' => 'Laporan Material Masuk '.self::TYPES[$p['type']],
            'period' => $periodLabel,
            'layout' => $layout,
            'layout_label' => self::LAYOUTS[$layout],
            'filters' => $this->describeFilters($filters),
            'generated_at' => now()->format('d/m/Y H:i'),
            'generated_by' => auth()->user()?->name ?? '-',
            'summary' => null,
            'truncated' => false,
            'notes' => ['Volume dijumlahkan per satuan; satuan yang berbeda tidak digabung.'],
        ];

        $report['total_transactions'] = $this->recap->totalTransactions($filters);

        if ($layout === 'detail') {
            $report += $this->detailTable($filters, $limit, $report['total_transactions']);
            $report['summary'] = $this->summaryTable($filters);
        } else {
            $report += $this->groupedTable($filters, $layout);
            $report['notes'][] = '*Pemakaian per unit dan pemakaian (m²) adalah jumlah nilai yang tercatat pada transaksi, bukan hasil rumus perusahaan.';
        }

        return $report;
    }

    /** @return array{0: Carbon, 1: Carbon, 2: string} */
    public function resolvePeriod(array $p): array
    {
        switch ($p['type']) {
            case 'daily':
                $d = Carbon::createFromFormat('Y-m-d', $p['date'])->startOfDay();

                return [$d, $d->copy(), $d->format('d/m/Y')];

            case 'weekly':
                preg_match('/^(\d{4})-W(\d{2})$/', $p['week'], $m);
                $from = Carbon::now()->setISODate((int) $m[1], (int) $m[2], 1)->startOfDay();
                $to = $from->copy()->addDays(6);

                return [$from, $to, 'Minggu ke-'.$m[2].' '.$m[1].' ('.$from->format('d/m/Y').' - '.$to->format('d/m/Y').')'];

            case 'monthly':
                $from = Carbon::createFromFormat('Y-m-d', $p['month'].'-01')->startOfDay();

                return [$from, $from->copy()->endOfMonth()->startOfDay(), $from->copy()->locale('id')->translatedFormat('F Y')];

            case 'yearly':
                $from = Carbon::create((int) $p['year'], 1, 1)->startOfDay();

                return [$from, Carbon::create((int) $p['year'], 12, 31)->startOfDay(), (string) $p['year']];

            default:
                $from = Carbon::parse($p['date_from'])->startOfDay();
                $to = Carbon::parse($p['date_to'])->startOfDay();

                return [$from, $to, $from->format('d/m/Y').' - '.$to->format('d/m/Y')];
        }
    }

    private function detailTable(array $f, ?int $limit, int $total): array
    {
        $q = MaterialReceipt::query()
            ->with(['cluster', 'material.category', 'unit'])
            ->where('transaction_date', '>=', $f['date_from'])
            ->where('transaction_date', '<=', $f['date_to'])
            ->when($f['cluster_id'], fn ($q, $id) => $q->where('cluster_id', (int) $id))
            ->when($f['category_id'], fn ($q, $id) => $q->whereHas('material', fn ($m) => $m->where('category_id', (int) $id)))
            ->when($f['material_id'], fn ($q, $id) => $q->where('material_id', (int) $id))
            ->orderBy('transaction_date')
            ->orderBy('id');

        if ($limit) {
            $q->limit($limit);
        }

        $rows = $q->get()->map(fn ($r) => [
            $r->transaction_number,
            $r->transaction_date->format('d/m/Y'),
            $r->cluster->cluster_name,
            $r->material->category->category_name,
            $r->material->material_name,
            [$r->volume, 3],
            $r->unit->unit_code,
            [$r->usage_per_unit, 3],
            [$r->usage_m2, 2],
        ])->all();

        return [
            'headings' => ['No. Transaksi', 'Tanggal', 'Lokasi Cluster', 'Kategori', 'Material', 'Volume', 'Satuan', 'Pemakaian per Unit', 'Pemakaian (m²)'],
            'col_decimals' => [null, null, null, null, null, 3, null, 3, 2],
            'rows' => $rows,
            'truncated' => $limit !== null && $total > $limit,
            'shown_rows' => count($rows),
        ];
    }

    /** Ringkasan di bawah rincian: total volume per material dan satuan. */
    private function summaryTable(array $f): array
    {
        $rows = $this->recap->rows($f, 'material')->map(fn ($r) => [
            $r['dims'][0], $r['dims'][1], $r['dims'][2],
            [$r['trx'], 0], [$r['volume'], 3],
        ])->all();

        return [
            'headings' => ['Kategori', 'Material', 'Satuan', 'Transaksi', 'Total Volume Masuk'],
            'col_decimals' => [null, null, null, 0, 3],
            'rows' => $rows,
        ];
    }

    private function groupedTable(array $f, string $layout): array
    {
        $dimCount = count(RecapService::columns($layout));

        $rows = $this->recap->rows($f, $layout)->map(fn ($r) => array_merge($r['dims'], [
            [$r['trx'], 0], [$r['volume'], 3], [$r['per_unit'], 3], [$r['m2'], 2],
        ]))->all();

        return [
            'headings' => array_merge(RecapService::columns($layout), ['Transaksi', 'Total Volume Masuk', 'Pemakaian per Unit*', 'Pemakaian (m²)*']),
            'col_decimals' => array_merge(array_fill(0, $dimCount, null), [0, 3, 3, 2]),
            'rows' => $rows,
            'shown_rows' => count($rows),
        ];
    }

    private function describeFilters(array $f): array
    {
        $out = [];
        if ($f['cluster_id']) {
            $out[] = 'Cluster: '.(Cluster::find($f['cluster_id'])?->cluster_name ?? '-');
        }
        if ($f['category_id']) {
            $out[] = 'Kategori: '.(Category::find($f['category_id'])?->category_name ?? '-');
        }
        if ($f['material_id']) {
            $out[] = 'Material: '.(Material::find($f['material_id'])?->material_name ?? '-');
        }

        return $out;
    }
}
