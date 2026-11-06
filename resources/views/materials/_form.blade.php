@php $isEdit = $material->exists; @endphp
<form method="POST" action="{{ $isEdit ? route('materials.update', $material) : route('materials.store') }}" novalidate>
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4">
            <label for="material_code" class="form-label">Kode material</label>
            <input type="text" id="material_code" name="material_code" value="{{ old('material_code', $material->material_code) }}" class="form-control @error('material_code') is-invalid @enderror" required>
            @error('material_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-8">
            <label for="material_name" class="form-label">Nama material</label>
            <input type="text" id="material_name" name="material_name" value="{{ old('material_name', $material->material_name) }}" class="form-control @error('material_name') is-invalid @enderror" required>
            @error('material_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="category_id" class="form-label">Kategori</label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) old('category_id', $material->category_id) === (string) $cat->id)>{{ $cat->category_name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="default_unit_id" class="form-label">Satuan standar</label>
            <select id="default_unit_id" name="default_unit_id" class="form-select @error('default_unit_id') is-invalid @enderror">
                <option value="">Tidak ditentukan</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) old('default_unit_id', $material->default_unit_id) === (string) $unit->id)>{{ $unit->unit_code }}</option>
                @endforeach
            </select>
            @error('default_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $material->status) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
