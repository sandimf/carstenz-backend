<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningCarstenszRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    public function authorize(): bool
    {
        // Kalau semua user boleh akses endpoint ini
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'date_of_birth'   => ['required', 'date'],
            'gender'          => ['required', 'in:male,female'],
            'nationality'     => ['required', 'string', 'max:100'],
            'passport_number' => ['required', 'string', 'max:100', 'unique:patients_cartensz,passport_number'],
            'email'           => ['required', 'email', 'max:255', 'unique:patients_cartensz,email'],
            'contact'         => ['required', 'string', 'max:50'],
            'answers'         => ['required'], 
            'passport_images' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'Nama wajib diisi.',
            'date_of_birth.required'   => 'Tanggal lahir wajib diisi.',
            'passport_number.required' => 'Nomor paspor wajib diisi.',
            'passport_number.unique'   => 'Nomor paspor sudah terdaftar.',
            'email.required'           => 'Email wajib diisi.',
            'email.email'              => 'Format email tidak valid.',
            'email.unique'             => 'Email sudah terdaftar.',
            'answers.required'         => 'Jawaban screening wajib diisi.',
        ];
    }
}
