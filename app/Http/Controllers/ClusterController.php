<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClusterRequest;
use App\Models\Cluster;
use Illuminate\Http\Request;

class ClusterController extends Controller
{
    public function index(Request $request)
    {
        $clusters = Cluster::query()
            ->withCount('receipts')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim($request->string('q')).'%';
                $q->where(fn ($w) => $w->where('cluster_name', 'like', $term)->orWhere('cluster_code', 'like', $term));
            })
            ->when(in_array($request->input('status'), ['aktif', 'nonaktif'], true), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('cluster_name')
            ->paginate(15)
            ->withQueryString();

        return view('clusters.index', compact('clusters'));
    }

    public function create()
    {
        return view('clusters.create', ['cluster' => new Cluster(['status' => 'aktif'])]);
    }

    public function store(ClusterRequest $request)
    {
        Cluster::create($request->validated());

        return redirect()->route('clusters.index')->with('success', 'Lokasi cluster ditambahkan.');
    }

    public function edit(Cluster $cluster)
    {
        return view('clusters.edit', compact('cluster'));
    }

    public function update(ClusterRequest $request, Cluster $cluster)
    {
        $cluster->update($request->validated());

        return redirect()->route('clusters.index')->with('success', 'Lokasi cluster diperbarui.');
    }

    public function toggleStatus(Cluster $cluster)
    {
        $cluster->update(['status' => $cluster->status === 'aktif' ? 'nonaktif' : 'aktif']);

        return back()->with('success', 'Status cluster "'.$cluster->cluster_name.'" menjadi '.$cluster->status.'.');
    }
}
