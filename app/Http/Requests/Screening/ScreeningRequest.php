<?php

namespace App\Http\Requests\Screening;

use Illuminate\Foundation\Http\FormRequest;

class ScreeningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ubah menjadi true supaya bisa dipakai
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:16'],
            'age' => ['required', 'integer', 'min:1'],
            'gender' => ['required', 'in:laki-laki,perempuan'],
            'date_of_birth' => ['required', 'date'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'religion' => ['required', 'string', 'max:50'],
            'marital_status' => ['required', 'string', 'max:50'],
            'occupation' => ['required', 'string', 'max:100'],
            'nationality' => ['required', 'string', 'max:50'],
            'blood_type' => ['required', 'string', 'max:3'],
            'address' => ['required', 'string', 'max:255'],
            'rt_rw' => ['required', 'string', 'max:10'],
            'village' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'valid_until' => ['required', 'string'],
            'tinggi_badan' => ['required', 'numeric'],
            'berat_badan' => ['required', 'numeric'],
            'contact' => ['required', 'string', 'max:20'],
            'ktp_images' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'answers' => ['required'],
            'answers.*.questioner_id' => ['required_with:answers', 'integer', 'exists:screening_questions,id'],
            'answers.*.answer' => ['required'],
        ];
    }

    /**
     * Custom messages for validation.
     */
    public function messages(): array
    {
        return [
            'required' => 'Kolom :attribute wajib diisi.',
            'email.email' => 'Kolom :attribute harus berupa alamat email yang valid.',
            'max' => 'Kolom :attribute tidak boleh lebih dari :max karakter.',
            'numeric' => 'Kolom :attribute harus berupa angka.',
            'date' => 'Kolom :attribute harus berupa tanggal yang valid.',
            'in' => 'Kolom :attribute harus salah satu dari pilihan yang tersedia.',
            'exists' => 'Kolom :attribute tidak valid.',
            'file' => 'Kolom :attribute harus berupa file.',
            'mimes' => 'Kolom :attribute harus berupa file dengan format: :values.',
            'answers.required' => 'Jawaban kuesioner wajib diisi.',
            'answers.*.questioner_id.required_with' => 'ID pertanyaan wajib diisi.',
            'answers.*.answer.required' => 'Jawaban untuk pertanyaan ini wajib diisi.',
        ];
    }
}
