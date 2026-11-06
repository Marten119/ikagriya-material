<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
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
        return view('users.create', ['user' => new User(['role' => 'staff', 'status' => 'aktif'])]);
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated()); // password di-hash oleh cast 'hashed' pada model

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
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

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna diperbarui.');
    }
}
