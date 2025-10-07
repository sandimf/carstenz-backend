<?php

namespace App\Models;

use App\Models\Users\Doctor;
use App\Models\Users\Nurse;
use App\Models\Users\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PhysicalExamination extends Model
{
    use HasFactory;

    protected $table = 'physical_examinations';

    protected $fillable = [
        'uuid',
        'patient_id',
        'nurse_id',
        'doctor_id',
        'blood_pressure',
        'heart_rate',
        'oxygen_saturation',
        'respiratory_rate',
        'body_temperature',
        'physical_assessment',
        'reason',
        'medical_advice',
        'health_status',
        'doctor_advice',
    ];

    protected $casts = [
        'heart_rate' => 'integer',
        'oxygen_saturation' => 'integer',
        'respiratory_rate' => 'integer',
        'body_temperature' => 'decimal:2',
    ];

    /**
     * Relasi ke Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relasi ke Nurse
     */
    public function nurse()
    {
        return $this->belongsTo(Nurse::class);
    }

    /**
     * Relasi ke Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Booted untuk UUID otomatis
     */
    protected static function boot()
    {
        parent::boot();

        // Secara otomatis menghasilkan UUID saat data dibuat
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
