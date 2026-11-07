<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
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
        return ['name' => 'nama', 'password' => 'password', 'username' => 'username'];
    }
}
