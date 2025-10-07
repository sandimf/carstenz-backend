<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\ScreeningAnswerCartensz;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScreeningCartensz extends Model
{
    use HasFactory;

    protected $table = 'screening_cartensz';

    protected $fillable = [
        'uuid',
        'patient_cartensz_id',
        'screening_status',
        'health_status',
        'health_check_status',
        'screening_date',
        'queue',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->uuid) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
    public function patient()
    {
        return $this->belongsTo(PatientCartensz::class, 'patient_cartensz_id');
    }
    public function answers()
    {
        return $this->hasMany(ScreeningAnswerCartensz::class, 'patient_cartensz_id');
    }

}
