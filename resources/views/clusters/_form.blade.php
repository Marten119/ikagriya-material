@php $isEdit = $cluster->exists; @endphp
<form method="POST" action="{{ $isEdit ? route('clusters.update', $cluster) : route('clusters.store') }}" novalidate>
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4">
            <label for="cluster_code" class="form-label">Kode cluster</label>
            <input type="text" id="cluster_code" name="cluster_code" value="{{ old('cluster_code', $cluster->cluster_code) }}" class="form-control @error('cluster_code') is-invalid @enderror" required>
            @error('cluster_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-8">
            <label for="cluster_name" class="form-label">Nama cluster</label>
            <input type="text" id="cluster_name" name="cluster_name" value="{{ old('cluster_name', $cluster->cluster_name) }}" class="form-control @error('cluster_name') is-invalid @enderror" required>
            @error('cluster_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
            <label for="description" class="form-label">Keterangan <span class="text-muted">(opsional)</span></label>
            <textarea id="description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $cluster->description) }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $cluster->status) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('clusters.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
