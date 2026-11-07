@php $isEdit = $user->exists; @endphp
<form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}" novalidate>
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="username" class="form-label">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" class="form-control @error('username') is-invalid @enderror" required>
            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email <span class="text-muted">(opsional)</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="role" class="form-label">Peran</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                @foreach (['admin' => 'Admin', 'staff' => 'Staff'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('role', $user->role) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $user->status) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label for="password" class="form-label">Password @if ($isEdit)<span class="text-muted">(kosongkan jika tidak diganti)</span>@endif</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @if(!$isEdit) required @endif>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Minimal 8 karakter, mengandung huruf dan angka.</div>
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">Konfirmasi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
        </div>
    </div>

    <div class="mt-3" id="cluster-access" style="display: none">
        <label class="form-label">Lokasi cluster yang boleh diinput <span class="text-muted">(khusus staff)</span></label>
        @php
            $selectedClusters = collect(old('clusters', $user->exists ? $user->clusters->pluck('id')->all() : []))->map(fn ($v) => (string) $v)->all();
        @endphp
        <div class="border rounded p-2 bg-white" style="max-height: 220px; overflow: auto">
            @forelse ($clusters as $c)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="clusters[]" id="cluster_{{ $c->id }}" value="{{ $c->id }}" @checked(in_array((string) $c->id, $selectedClusters, true))>
                    <label class="form-check-label" for="cluster_{{ $c->id }}">
                        {{ $c->cluster_code }} - {{ $c->cluster_name }}@if ($c->status !== 'aktif') <span class="text-muted">(nonaktif)</span>@endif
                    </label>
                </div>
            @empty
                <div class="text-muted small">Belum ada lokasi cluster. Tambahkan dahulu di menu Data Lokasi Cluster.</div>
            @endforelse
        </div>
        @error('clusters') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        @error('clusters.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        <div class="form-text">Staff hanya dapat mencatat material masuk pada cluster yang dicentang. Jika hanya satu cluster, kolom cluster pada form input terisi otomatis. Admin selalu dapat memilih semua cluster.</div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const role = document.getElementById('role');
    const box = document.getElementById('cluster-access');
    function sync() { box.style.display = role.value === 'staff' ? '' : 'none'; }
    role.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
