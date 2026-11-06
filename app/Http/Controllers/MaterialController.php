<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaterialRequest;
use App\Models\Category;
use App\Models\Material;
use App\Models\Unit;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::query()
            ->with(['category', 'defaultUnit'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $q->where(fn ($w) => $w->where('material_name', 'like', $term)->orWhere('material_code', 'like', $term));
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when(in_array($request->input('status'), ['aktif', 'nonaktif'], true), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('category_id')
            ->orderBy('material_name')
            ->paginate(15)
            ->withQueryString();

        return view('materials.index', [
            'materials' => $materials,
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('materials.create', $this->formData(new Material(['status' => 'aktif'])));
    }

    public function store(MaterialRequest $request)
    {
        Material::create($request->validated());

        return redirect()->route('materials.index')->with('success', 'Material ditambahkan.');
    }

    public function edit(Material $material)
    {
        return view('materials.edit', $this->formData($material));
    }

    public function update(MaterialRequest $request, Material $material)
    {
        $data = $request->validated();

        // Kategori dikunci jika material sudah dipakai transaksi, supaya rekap per kategori tidak bergeser.
        if ((int) $data['category_id'] !== $material->category_id && $material->receipts()->exists()) {
            return back()->withInput()->withErrors([
                'category_id' => 'Kategori tidak dapat diubah karena material ini sudah dipakai pada transaksi.',
            ]);
        }

        $material->update($data);

        return redirect()->route('materials.index')->with('success', 'Material diperbarui.');
    }

    public function toggleStatus(Material $material)
    {
        $material->update(['status' => $material->status === 'aktif' ? 'nonaktif' : 'aktif']);

        return back()->with('success', 'Status material "'.$material->material_name.'" menjadi '.$material->status.'.');
    }

    private function formData(Material $material): array
    {
        return [
            'material' => $material,
            'categories' => Category::orderBy('id')->get(),
            'units' => Unit::orderBy('unit_code')->get(),
        ];
    }
}
