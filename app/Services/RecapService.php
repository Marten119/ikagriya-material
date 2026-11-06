<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecapService
{
    public const GROUPS = [
        'material' => 'Per material',
        'category' => 'Per kategori',
        'cluster' => 'Per lokasi cluster',
        'period' => 'Per periode',
    ];

    public const PERIODS = [
        'day' => 'Harian',
        'week' => 'Mingguan',
        'month' => 'Bulanan',
        'year' => 'Tahunan',
    ];

    /** Query dasar + filter. Semua nilai filter di-bind (tidak ada input yang masuk ke SQL mentah). */
    public function base(array $f): Builder
    {
        return DB::table('material_receipts as r')
            ->join('materials as m', 'm.id', '=', 'r.material_id')
            ->join('categories as c', 'c.id', '=', 'm.category_id')
            ->join('clusters as cl', 'cl.id', '=', 'r.cluster_id')
            ->join('units as u', 'u.id', '=', 'r.unit_id')
            ->when(! empty($f['date_from']), fn ($q) => $q->where('r.transaction_date', '>=', $f['date_from']))
            ->when(! empty($f['date_to']), fn ($q) => $q->where('r.transaction_date', '<=', $f['date_to']))
            ->when(! empty($f['cluster_id']), fn ($q) => $q->where('r.cluster_id', (int) $f['cluster_id']))
            ->when(! empty($f['category_id']), fn ($q) => $q->where('m.category_id', (int) $f['category_id']))
            ->when(! empty($f['material_id']), fn ($q) => $q->where('r.material_id', (int) $f['material_id']))
            ->when(! empty($f['unit_id']), fn ($q) => $q->where('r.unit_id', (int) $f['unit_id']));
    }

    public static function columns(string $group): array
    {
        return match ($group) {
            'category' => ['Kategori', 'Satuan'],
            'cluster' => ['Lokasi cluster', 'Kategori', 'Material', 'Satuan'],
            'period' => ['Periode', 'Kategori', 'Material', 'Satuan'],
            default => ['Kategori', 'Material', 'Satuan'],
        };
    }

    /**
     * Hasil rekap. Satuan SELALU menjadi bagian pengelompokan, sehingga volume
     * dengan satuan berbeda tidak pernah dijumlahkan. Mode cluster dan periode
     * juga dipecah per material agar jumlah yang dijumlahkan sejenis.
     */
    public function rows(array $f, string $group, string $period = 'month'): Collection
    {
        $agg = 'COUNT(*) as trx_count, SUM(r.volume) as total_volume, SUM(r.usage_per_unit) as total_usage_per_unit, SUM(r.usage_m2) as total_usage_m2';
        $q = $this->base($f);

        switch ($group) {
            case 'category':
                $q->select('c.category_name', 'u.unit_code')->selectRaw($agg)
                    ->groupBy('c.id', 'c.category_name', 'u.id', 'u.unit_code')
                    ->orderBy('c.id')->orderBy('u.unit_code');
                break;

            case 'cluster':
                $q->select('cl.cluster_code', 'cl.cluster_name', 'c.category_name', 'm.material_code', 'm.material_name', 'u.unit_code')
                    ->selectRaw($agg)
                    ->groupBy('cl.id', 'cl.cluster_code', 'cl.cluster_name', 'c.id', 'c.category_name', 'm.id', 'm.material_code', 'm.material_name', 'u.id', 'u.unit_code')
                    ->orderBy('cl.cluster_name')->orderBy('c.id')->orderBy('m.material_name')->orderBy('u.unit_code');
                break;

            case 'period':
                $q->selectRaw($this->periodExpr($period).' as period_label')
                    ->addSelect('c.category_name', 'm.material_code', 'm.material_name', 'u.unit_code')
                    ->selectRaw($agg)
                    ->groupBy('period_label', 'c.id', 'c.category_name', 'm.id', 'm.material_code', 'm.material_name', 'u.id', 'u.unit_code')
                    ->orderBy('period_label')->orderBy('c.id')->orderBy('m.material_name')->orderBy('u.unit_code');
                break;

            default:
                $q->select('c.category_name', 'm.material_code', 'm.material_name', 'u.unit_code')->selectRaw($agg)
                    ->groupBy('c.id', 'c.category_name', 'm.id', 'm.material_code', 'm.material_name', 'u.id', 'u.unit_code')
                    ->orderBy('c.id')->orderBy('m.material_name')->orderBy('u.unit_code');
        }

        return $q->get()->map(fn ($row) => [
            'dims' => $this->dims($group, $period, $row),
            'trx' => (int) $row->trx_count,
            'volume' => $row->total_volume,
            'per_unit' => $row->total_usage_per_unit,
            'm2' => $row->total_usage_m2,
        ]);
    }

    public function totalTransactions(array $f): int
    {
        return $this->base($f)->count();
    }

    /** Jumlah transaksi per hari atau per bulan, termasuk periode kosong (0), untuk grafik. */
    public function timeline(array $f, string $granularity, Carbon $from, Carbon $to): array
    {
        $expr = $this->periodExpr($granularity === 'day' ? 'day' : 'month');
        $counts = $this->base($f)->selectRaw($expr.' as p, COUNT(*) as c')->groupBy('p')->get()->pluck('c', 'p');

        $labels = [];
        $data = [];

        if ($granularity === 'day') {
            foreach (CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay()) as $d) {
                $labels[] = $d->format('d/m');
                $data[] = (int) ($counts[$d->format('Y-m-d')] ?? 0);
            }
        } else {
            foreach (CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to->copy()->startOfMonth()) as $d) {
                $labels[] = $d->format('m/Y');
                $data[] = (int) ($counts[$d->format('Y-m')] ?? 0);
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function paginate(Collection $items, int $perPage = 25): LengthAwarePaginator
    {
        $page = max(1, (int) request()->query('page', 1));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    private function dims(string $group, string $period, object $row): array
    {
        $material = isset($row->material_code) ? $row->material_code.' - '.$row->material_name : null;

        return match ($group) {
            'category' => [$row->category_name, $row->unit_code],
            'cluster' => [$row->cluster_code.' - '.$row->cluster_name, $row->category_name, $material, $row->unit_code],
            'period' => [$this->periodLabel($period, $row->period_label), $row->category_name, $material, $row->unit_code],
            default => [$row->category_name, $material, $row->unit_code],
        };
    }

    /** Ekspresi periode dari daftar putih tetap; tidak pernah memakai input pengguna. */
    private function periodExpr(string $period): string
    {
        return match ($period) {
            'day' => "DATE_FORMAT(r.transaction_date, '%Y-%m-%d')",
            'week' => "DATE_FORMAT(r.transaction_date, '%x-W%v')",
            'year' => "DATE_FORMAT(r.transaction_date, '%Y')",
            default => "DATE_FORMAT(r.transaction_date, '%Y-%m')",
        };
    }

    private function periodLabel(string $period, string $value): string
    {
        return match ($period) {
            'day' => Carbon::createFromFormat('Y-m-d', $value)->format('d/m/Y'),
            'month' => Carbon::createFromFormat('Y-m', $value)->format('m/Y'),
            default => $value,
        };
    }
}
