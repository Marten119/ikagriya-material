<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialReceiptRequest;
use App\Models\Category;
use App\Models\Cluster;
use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\Unit;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class MaterialReceiptController extends Controller
{
    public function __construct(private ReceiptService $service)
    {
    }

    public function index(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $receipts = MaterialReceipt::query()
            ->with(['cluster', 'material.category', 'unit', 'creator'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $q->where(function ($w) use ($term) {
                    $w->where('transaction_number', 'like', $term)
                        ->orWhereHas('material', fn ($m) => $m->where('material_name', 'like', $term)->orWhere('material_code', 'like', $term));
                });
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->input('date_to')))
            ->when($request->filled('cluster_id'), fn ($q) => $q->where('cluster_id', $request->integer('cluster_id')))
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas('material', fn ($m) => $m->where('category_id', $request->integer('category_id'))))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('receipts.index', $this->formData(new MaterialReceipt(['usage_per_unit' => 0, 'usage_m2' => 0])) + [
            'receipts' => $receipts,
            'filterClusters' => Cluster::orderBy('cluster_name')->get(),
            'token' => (string) Str::uuid(),
            'mode' => 'create',
            'cancelUrl' => route('dashboard'),
        ]);
    }

    public function store(MaterialReceiptRequest $request)
    {
        [$receipt, $created] = $this->service->create($request->validated(), $request->user());

        $message = $created
            ? 'Transaksi '.$receipt->transaction_number.' berhasil disimpan.'
            : 'Transaksi ini sudah tersimpan sebelumnya ('.$receipt->transaction_number.'). Tidak dibuat ganda.';

        return redirect()->route('receipts.index')->with('success', $message);
    }

    public function edit(MaterialReceipt $receipt)
    {
        Gate::authorize('update', $receipt);

        $receipt->load('material', 'creator');

        return view('receipts.edit', $this->formData($receipt) + [
            'mode' => 'edit',
            'cancelUrl' => route('receipts.index'),
        ]);
    }

    public function update(MaterialReceiptRequest $request, MaterialReceipt $receipt)
    {
        $changed = $this->service->update($receipt, $request->validated());

        return redirect()->route('receipts.index')->with(
            'success',
            $changed ? 'Transaksi '.$receipt->transaction_number.' diperbarui.' : 'Tidak ada perubahan data.'
        );
    }

    public function destroy(MaterialReceipt $receipt)
    {
        Gate::authorize('delete', $receipt);

        $number = $receipt->transaction_number;
        $this->service->delete($receipt);

        return redirect()->route('receipts.index')->with('success', 'Transaksi '.$number.' dihapus dan tercatat pada log aktivitas.');
    }

    /** Daftar material aktif berdasarkan kategori (untuk dropdown bertingkat). */
    public function materials(Request $request)
    {
        $request->validate(['category_id' => ['required', 'integer', 'exists:categories,id']]);

        return Material::active()
            ->where('category_id', $request->integer('category_id'))
            ->orderBy('material_name')
            ->get(['id', 'material_code', 'material_name', 'default_unit_id']);
    }

    private function formData(MaterialReceipt $receipt): array
    {
        $oldCategory = session()->getOldInput('category_id');
        $categoryId = $oldCategory ?: ($receipt->exists ? $receipt->material->category_id : null);

        $materialOptions = $categoryId
            ? Material::where('category_id', (int) $categoryId)
                ->where(function ($q) use ($receipt) {
                    $q->where('status', 'aktif');
                    if ($receipt->exists) {
                        $q->orWhere('id', $receipt->material_id);
                    }
                })
                ->orderBy('material_name')
                ->get()
            : collect();

        // Admin melihat semua cluster aktif; staff hanya cluster yang ditetapkan admin.
        $user = auth()->user();
        $clusters = Cluster::where(function ($q) use ($receipt) {
            $q->where('status', 'aktif');
            if ($receipt->exists) {
                $q->orWhere('id', $receipt->cluster_id);
            }
        })
            ->when(! $user->isAdmin(), fn ($q) => $q->whereIn('id', $user->assignedClusterIds()))
            ->orderBy('cluster_name')
            ->get();

        $creating = ! $receipt->exists;
        $lockedCluster = (! $user->isAdmin() && $creating && $clusters->count() === 1) ? $clusters->first() : null;
        $noCluster = ! $user->isAdmin() && $creating && $clusters->isEmpty();

        return [
            'receipt' => $receipt,
            'categories' => Category::orderBy('id')->get(),
            'clusters' => $clusters,
            'lockedCluster' => $lockedCluster,
            'noCluster' => $noCluster,
            'units' => Unit::where(function ($q) use ($receipt) {
                $q->where('status', 'aktif');
                if ($receipt->exists) {
                    $q->orWhere('id', $receipt->unit_id);
                }
            })->orderBy('unit_code')->get(),
            'materialOptions' => $materialOptions,
        ];
    }
}
