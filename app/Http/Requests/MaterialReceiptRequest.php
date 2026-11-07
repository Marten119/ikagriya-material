<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $receipt = $this->route('receipt');

        return $receipt ? $this->user()->can('update', $receipt) : (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        // Terima koma atau titik sebagai pemisah desimal. Pemisah ribuan tidak diterima.
        $merge = [];
        foreach (['volume', 'usage_per_unit', 'usage_m2'] as $f) {
            $merge[$f] = str_replace(',', '.', trim((string) $this->input($f)));
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        $receipt = $this->route('receipt'); // null saat transaksi baru
        $categoryId = $this->input('category_id');

        $activeOrCurrent = function (string $column, $currentId) {
            return function ($query) use ($column, $currentId) {
                $query->where(function ($w) use ($column, $currentId) {
                    $w->where('status', 'aktif');
                    if ($currentId) {
                        $w->orWhere($column, $currentId);
                    }
                });
            };
        };

        $rules = [
            'transaction_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'cluster_id' => ['required', 'integer', Rule::exists('clusters', 'id')->where($activeOrCurrent('id', $receipt?->cluster_id))],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'material_id' => [
                'required', 'integer',
                Rule::exists('materials', 'id')
                    ->where('category_id', $categoryId)
                    ->where($activeOrCurrent('id', $receipt?->material_id)),
            ],
            'volume' => ['required', 'bail', 'numeric', 'regex:/^\d{1,11}(\.\d{1,3})?$/', 'gt:0'],
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')->where($activeOrCurrent('id', $receipt?->unit_id))],
            'usage_per_unit' => ['required', 'bail', 'numeric', 'regex:/^\d{1,11}(\.\d{1,3})?$/'],
            'usage_m2' => ['required', 'bail', 'numeric', 'regex:/^\d{1,12}(\.\d{1,2})?$/'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        // Staff hanya boleh mencatat pada cluster yang ditetapkan admin (dicek di server).
        if (! $this->user()->isAdmin()) {
            $rules['cluster_id'][] = Rule::in($this->user()->assignedClusterIds());
        }

        if (! $receipt) {
            $rules['submit_token'] = ['required', 'uuid'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer' => ':attribute tidak valid.',
            'exists' => ':attribute tidak valid, tidak aktif, atau tidak sesuai kategori.',
            'numeric' => ':attribute harus berupa angka.',
            'date_format' => ':attribute harus berformat tahun-bulan-tanggal.',
            'before_or_equal' => ':attribute tidak boleh melebihi hari ini.',
            'gt' => ':attribute harus lebih dari 0.',
            'max' => ':attribute terlalu panjang.',
            'uuid' => 'Sesi form tidak valid. Muat ulang halaman.',
            'cluster_id.in' => 'Lokasi cluster ini tidak ditetapkan untuk akun Anda.',
            'volume.regex' => 'Volume tidak valid: tidak boleh negatif, maksimal 3 angka desimal, tanpa pemisah ribuan.',
            'usage_per_unit.regex' => 'Pemakaian per unit tidak valid: tidak boleh negatif, maksimal 3 angka desimal, tanpa pemisah ribuan.',
            'usage_m2.regex' => 'Pemakaian (m²) tidak valid: tidak boleh negatif, maksimal 2 angka desimal, tanpa pemisah ribuan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'transaction_date' => 'Tanggal material masuk',
            'cluster_id' => 'Lokasi cluster',
            'category_id' => 'Kategori material',
            'material_id' => 'Nama material',
            'volume' => 'Volume',
            'unit_id' => 'Satuan',
            'usage_per_unit' => 'Pemakaian per unit',
            'usage_m2' => 'Pemakaian (m²)',
            'notes' => 'Catatan',
        ];
    }
}
