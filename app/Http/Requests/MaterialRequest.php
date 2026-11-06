<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'material_code' => trim((string) $this->input('material_code')),
            'material_name' => trim((string) $this->input('material_name')),
            'default_unit_id' => $this->input('default_unit_id') ?: null,
        ]);
    }

    public function rules(): array
    {
        $material = $this->route('material'); // null saat tambah baru

        return [
            'material_code' => ['required', 'string', 'max:40', Rule::unique('materials', 'material_code')->ignore($material)],
            'material_name' => [
                'required', 'string', 'max:150',
                Rule::unique('materials', 'material_name')->where('category_id', $this->input('category_id'))->ignore($material),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'default_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function messages(): array
    {
        return [
            'material_code.unique' => 'Kode material sudah digunakan.',
            'material_name.unique' => 'Nama material sudah ada pada kategori ini.',
        ];
    }

    public function attributes(): array
    {
        return [
            'material_code' => 'kode material',
            'material_name' => 'nama material',
            'category_id' => 'kategori',
            'default_unit_id' => 'satuan standar',
        ];
    }
}
