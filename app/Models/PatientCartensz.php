<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\ScreeningCartensz;
use App\Models\ScreeningAnswerCartensz;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientCartensz extends Model
{
    use HasFactory;

    protected $table = 'patients_cartensz';

    protected $fillable = [
        'uuid',
        'name',
        'date_of_birth',
        'gender',
        'nationality',
        'passport_number',
        'email',
        'contact',
        'passport_images',
    ];

    // Biar uuid otomatis keisi saat create
    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relasi ke screening
    public function screenings()
    {
        return $this->hasMany(ScreeningCartensz::class, 'patient_cartensz_id');
    }
            public function screeningCartensz()
        {
            return $this->hasOne(ScreeningCartensz::class, 'patient_cartensz_id');
        }
        public function answers()
{
    return $this->hasMany(ScreeningAnswerCartensz::class, 'patient_id');
}
}
