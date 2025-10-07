<?php

namespace App\Http\Requests\Screening;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnswerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // atau cek otorisasi khusus di sini
    }

    public function rules(): array
    {
        return [
            'answer' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'answer.required' => 'Jawaban wajib diisi.',
            'answer.string' => 'Jawaban harus berupa teks.',
        ];
    }
}
