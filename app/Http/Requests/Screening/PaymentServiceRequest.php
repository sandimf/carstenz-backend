<?php

namespace App\Http\Requests\Screening;

use Illuminate\Foundation\Http\FormRequest;

class PaymentServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // ganti sesuai kebutuhan, misal: return auth()->check();
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
            'patient_id' => 'nullable|exists:patients,id',
            'type' => 'required|string|max:255',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:100',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'amount_service_id' => 'nullable|exists:amount_services,id',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'patient_id.exists' => 'Patient tidak ditemukan.',
            'type.required' => 'Tipe layanan wajib diisi.',
            'type.max' => 'Tipe layanan maksimal 255 karakter.',
            'amount_paid.required' => 'Jumlah pembayaran wajib diisi.',
            'amount_paid.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'amount_paid.min' => 'Jumlah pembayaran minimal 0.',
            'payment_method.required' => 'Metode pembayaran wajib diisi.',
            'payment_method.max' => 'Metode pembayaran maksimal 100 karakter.',
            'payment_proof.file' => 'Bukti pembayaran harus berupa file.',
            'payment_proof.mimes' => 'Bukti pembayaran harus berupa jpg, jpeg, png, atau pdf.',
            'payment_proof.max' => 'Ukuran file maksimum 5 MB.',
            'amount_service_id.exists' => 'AmountService tidak ditemukan.',
        ];
    }
}
