<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cluster;
use App\Models\Material;
use App\Models\Unit;
use App\Services\RecapService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecapController extends Controller
{
    public function index(Request $request, RecapService $recap)
    {
        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'cluster_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'material_id' => ['nullable', 'integer'],
            'unit_id' => ['nullable', 'integer'],
            'group_by' => ['nullable', Rule::in(array_keys(RecapService::GROUPS))],
            'period' => ['nullable', Rule::in(array_keys(RecapService::PERIODS))],
        ]);

        $group = $data['group_by'] ?? 'material';
        $period = $data['period'] ?? 'month';

        $rows = $recap->rows($data, $group, $period);

        return view('recap.index', [
            'group' => $group,
            'period' => $period,
            'columns' => RecapService::columns($group),
            'rows' => $recap->paginate($rows, 25),
            'totalTransactions' => $recap->totalTransactions($data),
            'clusters' => Cluster::orderBy('cluster_name')->get(),
            'categories' => Category::orderBy('id')->get(),
            'materials' => Material::with('category')->orderBy('category_id')->orderBy('material_name')->get()->groupBy(fn ($m) => $m->category->category_name),
            'units' => Unit::orderBy('unit_code')->get(),
        ]);
    }
}
