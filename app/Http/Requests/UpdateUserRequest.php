<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
            'clusters' => [Rule::requiredIf(fn () => $this->input('role') === 'staff'), 'nullable', 'array'],
            'clusters.*' => ['integer', 'exists:clusters,id'],
        ];
    }

    public function messages(): array
    {
        return ['clusters.required' => 'Pilih minimal satu lokasi cluster untuk akun staff.'];
    }

    public function attributes(): array
    {
        return ['name' => 'nama'];
    }
}
