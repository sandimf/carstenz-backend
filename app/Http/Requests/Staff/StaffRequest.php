<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:30',
            'email' => 'required|string|email|max:30|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:admin,cashier,nurse,doctor,manager,warehouse,manager,frontoffice',
            'nik' => 'required|string',
            'date_of_birth' => 'required|date',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15|min:5',
            'signature' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Silakan isi nama.',
            'name.string' => 'Nama harus berupa teks yang valid.',
            'name.max' => 'Panjang nama maksimal 30 karakter.',
            'date_of_birth.required' => 'Silakan isi tanggal lahir.',
            'address.required' => 'Silakan isi alamat.',
            'phone.required' => 'Silakan isi nomor telepon.',
            'email.required' => 'Silakan isi alamat email.',
            'email.string' => 'Alamat email harus berupa teks yang valid.',
            'role.required' => 'Silakan tentukan peran pengguna.',
            'signature.required' => 'Silakan unggah tanda tangan digital.',
            'signature.string' => 'Tanda tangan digital harus berupa teks yang valid.',
        ];
    }
}
