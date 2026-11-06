<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cluster_code' => trim((string) $this->input('cluster_code')),
            'cluster_name' => trim((string) $this->input('cluster_name')),
        ]);
    }

    public function rules(): array
    {
        $cluster = $this->route('cluster'); // null saat tambah baru

        return [
            'cluster_code' => ['required', 'string', 'max:30', Rule::unique('clusters', 'cluster_code')->ignore($cluster)],
            'cluster_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }

    public function messages(): array
    {
        return ['cluster_code.unique' => 'Kode cluster sudah digunakan.'];
    }

    public function attributes(): array
    {
        return ['cluster_code' => 'kode cluster', 'cluster_name' => 'nama cluster', 'description' => 'keterangan'];
    }
}
