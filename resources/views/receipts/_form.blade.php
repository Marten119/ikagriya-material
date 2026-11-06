@php
    $isEdit = $mode === 'edit';
    $selCategory = old('category_id', $isEdit ? $receipt->material->category_id : null);
    $selMaterial = old('material_id', $receipt->material_id);
    $today = now()->format('Y-m-d');
@endphp
<form id="receipt-form" method="POST" action="{{ $isEdit ? route('receipts.update', $receipt) : route('receipts.store') }}"
      data-mode="{{ $mode }}" data-today="{{ $today }}" data-materials-url="{{ route('receipts.materials') }}">
    @csrf
    @if ($isEdit) @method('PUT') @else <input type="hidden" name="submit_token" value="{{ $token }}"> @endif
    @error('submit_token') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    <div class="row g-3">
        <div class="col-md-3">
            <label for="transaction_date" class="form-label">Tanggal material masuk</label>
            <input type="date" id="transaction_date" name="transaction_date" max="{{ $today }}"
                   value="{{ old('transaction_date', $isEdit ? $receipt->transaction_date->format('Y-m-d') : $today) }}"
                   class="form-control @error('transaction_date') is-invalid @enderror" required>
            @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-5">
            <label for="cluster_id" class="form-label">Lokasi cluster</label>
            <select id="cluster_id" name="cluster_id" class="form-select @error('cluster_id') is-invalid @enderror" required>
                <option value="">Pilih lokasi cluster</option>
                @foreach ($clusters as $c)
                    <option value="{{ $c->id }}" @selected((string) old('cluster_id', $receipt->cluster_id) === (string) $c->id)>{{ $c->cluster_code }} - {{ $c->cluster_name }}</option>
                @endforeach
            </select>
            @error('cluster_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="category_id" class="form-label">Kategori material</label>
            <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $selCategory === (string) $cat->id)>{{ $cat->category_name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="material_id" class="form-label">Nama material</label>
            <select id="material_id" name="material_id" class="form-select @error('material_id') is-invalid @enderror" required @disabled(! $selCategory)>
                @if (! $selCategory)
                    <option value="">Pilih kategori dahulu</option>
                @else
                    <option value="">Pilih material</option>
                    @foreach ($materialOptions as $m)
                        <option value="{{ $m->id }}" data-unit="{{ $m->default_unit_id }}" @selected((string) $selMaterial === (string) $m->id)>{{ $m->material_code }} - {{ $m->material_name }}</option>
                    @endforeach
                @endif
            </select>
            @error('material_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="volume" class="form-label">Volume</label>
            <input type="text" inputmode="decimal" id="volume" name="volume" autocomplete="off"
                   pattern="[0-9]+([.,][0-9]+)?" title="Angka positif, desimal dengan koma atau titik"
                   value="{{ old('volume', $isEdit ? \App\Support\Fmt::input($receipt->volume) : '') }}"
                   class="form-control @error('volume') is-invalid @enderror" required>
            @error('volume') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-3">
            <label for="unit_id" class="form-label">Satuan</label>
            <select id="unit_id" name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                <option value="">Pilih satuan</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}" @selected((string) old('unit_id', $receipt->unit_id) === (string) $u->id)>{{ $u->unit_code }}</option>
                @endforeach
            </select>
            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="usage_per_unit" class="form-label">Pemakaian per unit</label>
            <input type="text" inputmode="decimal" id="usage_per_unit" name="usage_per_unit" autocomplete="off"
                   pattern="[0-9]+([.,][0-9]+)?" title="Angka nol atau lebih, desimal dengan koma atau titik"
                   value="{{ old('usage_per_unit', \App\Support\Fmt::input($receipt->usage_per_unit ?? 0)) }}"
                   class="form-control @error('usage_per_unit') is-invalid @enderror" required>
            @error('usage_per_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="usage_m2" class="form-label">Pemakaian (m²)</label>
            <input type="text" inputmode="decimal" id="usage_m2" name="usage_m2" autocomplete="off"
                   pattern="[0-9]+([.,][0-9]+)?" title="Angka nol atau lebih, desimal dengan koma atau titik"
                   value="{{ old('usage_m2', \App\Support\Fmt::input($receipt->usage_m2 ?? 0)) }}"
                   class="form-control @error('usage_m2') is-invalid @enderror" required>
            @error('usage_m2') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-4">
            <label for="notes" class="form-label">Catatan <span class="text-muted">(opsional)</span></label>
            <input type="text" id="notes" name="notes" maxlength="500" value="{{ old('notes', $receipt->notes) }}"
                   class="form-control @error('notes') is-invalid @enderror">
            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" id="btn-save" class="btn btn-primary">Simpan</button>
        <button type="button" id="btn-reset" class="btn btn-outline-secondary">Reset</button>
        <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('receipt-form');
    if (!form) return;
    const cat = form.querySelector('#category_id');
    const mat = form.querySelector('#material_id');
    const unit = form.querySelector('#unit_id');
    const btn = form.querySelector('#btn-save');

    function setOptions(list) {
        mat.innerHTML = '';
        const first = document.createElement('option');
        first.value = '';
        first.textContent = list === null ? 'Pilih kategori dahulu' : 'Pilih material';
        mat.appendChild(first);
        (list || []).forEach(function (m) {
            const op = document.createElement('option');
            op.value = m.id;
            op.textContent = m.material_code + ' - ' + m.material_name;
            op.dataset.unit = m.default_unit_id || '';
            mat.appendChild(op);
        });
        mat.disabled = list === null;
    }

    cat.addEventListener('change', async function () {
        if (!cat.value) { setOptions(null); return; }
        mat.disabled = true;
        try {
            const res = await fetch(form.dataset.materialsUrl + '?category_id=' + encodeURIComponent(cat.value), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('gagal');
            setOptions(await res.json());
        } catch (e) {
            setOptions([]);
            alert('Daftar material gagal dimuat. Muat ulang halaman lalu coba lagi.');
        }
        mat.disabled = false;
    });

    // Satuan standar diisikan saat material dipilih; tetap bisa diubah manual.
    mat.addEventListener('change', function () {
        const opt = mat.selectedOptions[0];
        if (opt && opt.dataset.unit) unit.value = opt.dataset.unit;
    });

    form.querySelector('#btn-reset').addEventListener('click', function () {
        if (form.dataset.mode === 'edit') { window.location.reload(); return; }
        form.querySelector('#transaction_date').value = form.dataset.today;
        form.querySelector('#cluster_id').value = '';
        cat.value = '';
        setOptions(null);
        unit.value = '';
        form.querySelector('#volume').value = '';
        form.querySelector('#usage_per_unit').value = '0';
        form.querySelector('#usage_m2').value = '0';
        form.querySelector('#notes').value = '';
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
    });

    // Cegah kirim ganda: tombol dikunci setelah submit pertama.
    form.addEventListener('submit', function (e) {
        if (form.dataset.submitted === '1') { e.preventDefault(); return; }
        form.dataset.submitted = '1';
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });
    window.addEventListener('pageshow', function () {
        form.dataset.submitted = '';
        btn.disabled = false;
        btn.textContent = 'Simpan';
    });
})();
</script>
@endpush
