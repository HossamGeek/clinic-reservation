<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(['PATIENT', 'DOCTOR'])],

            'gender' => [Rule::requiredIf($this->role === 'PATIENT'), 'nullable', Rule::in(['MALE', 'FEMALE'])],
            'date_of_birth' => [Rule::requiredIf($this->role === 'PATIENT'), 'nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'medical_history' => ['nullable', 'string'],

            'specialty' => [Rule::requiredIf($this->role === 'DOCTOR'), 'nullable', 'string', 'max:100'],
            'available_time' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string'],
        ];
    }
}
