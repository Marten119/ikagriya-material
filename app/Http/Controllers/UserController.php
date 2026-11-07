<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Cluster;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('clusters')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('username', 'like', $term));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create', [
            'user' => new User(['role' => 'staff', 'status' => 'aktif']),
            'clusters' => $this->clusterList(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $user = User::create(Arr::except($data, ['clusters'])); // password di-hash oleh cast 'hashed'
            $this->syncClusters($user, $data);
        });

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $user->load('clusters');

        return view('users.edit', [
            'user' => $user,
            'clusters' => $this->clusterList(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Admin tidak boleh menurunkan atau menonaktifkan akunnya sendiri.
        if ($user->is($request->user()) && ($data['role'] !== 'admin' || $data['status'] !== 'aktif')) {
            return back()->withInput()->withErrors([
                'role' => 'Anda tidak dapat mengubah peran atau menonaktifkan akun Anda sendiri.',
            ]);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update(Arr::except($data, ['clusters']));
            $this->syncClusters($user, $data);
        });

        return redirect()->route('users.index')->with('success', 'Data pengguna diperbarui.');
    }

    private function clusterList()
    {
        return Cluster::orderBy('cluster_name')->get();
    }

    /** Hanya staff yang memakai penetapan cluster; admin otomatis boleh semua sehingga daftarnya dikosongkan. */
    private function syncClusters(User $user, array $data): void
    {
        $ids = $data['role'] === 'staff' ? array_map('intval', $data['clusters'] ?? []) : [];

        $old = $user->clusters()->pluck('cluster_code')->sort()->values()->all();
        $user->clusters()->sync($ids);
        $new = Cluster::whereIn('id', $ids)->pluck('cluster_code')->sort()->values()->all();

        if ($old !== $new) {
            ActivityLogger::log('update', 'user_clusters', $user->id, ['clusters' => $old], ['clusters' => $new]);
        }
    }
}
