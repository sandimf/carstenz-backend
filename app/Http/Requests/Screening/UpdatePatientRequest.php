<?php

namespace App\Http\Requests\Screening;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // atau cek otorisasi sesuai kebutuhan
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female',
            'tinggi_badan' => 'sometimes|numeric',
            'berat_badan' => 'sometimes|numeric',
            'occupation' => 'sometimes|string|max:255',
            'emergency_contact' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'date_of_birth.date' => 'Tanggal lahir harus berupa format tanggal yang valid.',
            'gender.in' => 'Gender hanya boleh male atau female.',
            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'occupation.max' => 'Pekerjaan maksimal 255 karakter.',
            'emergency_contact.max' => 'Kontak darurat maksimal 255 karakter.',
        ];
    }
}
