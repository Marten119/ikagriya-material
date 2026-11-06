<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use App\Models\MaterialReceipt;
use App\Services\RecapService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, RecapService $recap)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'cluster_id' => ['nullable', 'integer'],
        ]);

        // Bawaan: bulan berjalan sampai hari ini.
        $from = ! empty($data['date_from']) ? Carbon::parse($data['date_from'])->startOfDay() : now()->startOfMonth();
        $to = ! empty($data['date_to']) ? Carbon::parse($data['date_to'])->startOfDay() : now()->startOfDay();

        $filters = [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'cluster_id' => $data['cluster_id'] ?? null,
        ];

        $days = (int) $from->diffInDays($to) + 1;
        $granularity = $days <= 62 ? 'day' : 'month';

        $summary = [
            'transactions' => $recap->base($filters)->count(),
            'categories' => $recap->base($filters)->distinct()->count('m.category_id'),
            'clusters' => $recap->base($filters)->distinct()->count('r.cluster_id'),
        ];

        $recent = MaterialReceipt::query()
            ->with(['cluster', 'material', 'unit'])
            ->whereDate('transaction_date', '>=', $filters['date_from'])
            ->whereDate('transaction_date', '<=', $filters['date_to'])
            ->when($filters['cluster_id'], fn ($q, $id) => $q->where('cluster_id', (int) $id))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('dashboard', [
            'filters' => $filters,
            'clusters' => Cluster::orderBy('cluster_name')->get(),
            'summary' => $summary,
            'timeline' => $recap->timeline($filters, $granularity, $from, $to),
            'granularity' => $granularity,
            'categoryRecap' => $recap->rows($filters, 'category'),
            'recent' => $recent,
        ]);
    }
}
