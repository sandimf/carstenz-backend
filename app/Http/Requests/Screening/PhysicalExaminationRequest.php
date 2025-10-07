<?php

namespace App\Http\Requests\Screening;

use Illuminate\Foundation\Http\FormRequest;

class PhysicalExaminationRequest extends FormRequest
{
    public function authorize()
    {
        // Atur ke true jika semua user yang login boleh membuat
        return true;
    }

    public function rules()
    {
        return [
            'nurse_id' => 'required|exists:nurses,id',
            'patient_id' => 'required|exists:patients,id',
            'blood_pressure' => 'required|string',
            'heart_rate' => 'required|integer',
            'oxygen_saturation' => 'required|integer',
            'respiratory_rate' => 'required|integer',
            'body_temperature' => 'required|numeric',
            'physical_assessment' => 'required|string',
            'reason' => 'nullable|string',
            'medical_advice' => 'nullable|string',
            'health_status' => 'required|in:sehat,tidak_sehat_dengan_pendamping,tidak_sehat',
            'consultation' => 'nullable|boolean',
            'medical_accompaniment' => 'nullable|in:pendampingan_perawat,pendampingan_paramedis,pendampingan_dokter',
        ];
    }

    public function messages()
    {
        return [
            'nurse_id.required' => 'Perawat harus dipilih',
            'patient_id.required' => 'Pasien harus dipilih',
            'blood_pressure.required' => 'Tekanan darah harus diisi',
            'heart_rate.required' => 'Denyut jantung harus diisi',
            'oxygen_saturation.required' => 'Saturasi oksigen harus diisi',
            'respiratory_rate.required' => 'Laju pernapasan harus diisi',
            'body_temperature.required' => 'Suhu tubuh harus diisi',
            'physical_assessment.required' => 'Hasil pemeriksaan fisik harus diisi',
            'health_status.required' => 'Status kesehatan harus dipilih',
        ];
    }
}
