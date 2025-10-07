<?php

namespace App\Models\Users;

use App\Models\ClinicServices\Screening\Screening;
use App\Models\ClinicServices\Screening\ScreeningAnswer;
use App\Models\Payment\AmountService;
use App\Models\PhysicalExamination;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'patients';

    protected $fillable = [
        'uuid',
        'user_id',
        'nik',
        'name',
        'place_of_birth',
        'date_of_birth',
        'rt_rw',
        'address',
        'village',
        'district',
        'religion',
        'marital_status',
        'occupation',
        'nationality',
        'gender',
        'email',
        'blood_type',
        'age',
        'contact',
        'ktp_images',
        'tinggi_badan',
        'berat_badan',
        'archived_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'archived_at' => 'datetime',
        'tinggi_badan' => 'decimal:2',
        'berat_badan' => 'decimal:2',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Screening
    public function screenings()
    {
        return $this->hasMany(Screening::class);
    }

    public function screeningAnswerQ1()
    {
        return $this->hasOne(ScreeningAnswer::class, 'patient_id')
            ->where('question_id', 1);
    }

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

    public function answers()
    {
        return $this->hasMany(ScreeningAnswer::class, 'patient_id');
    }

    public function physicalExaminations()
    {
        return $this->hasMany(PhysicalExamination::class, 'patient_id');
    }

    public function amountServices()
    {
        return $this->belongsToMany(AmountService::class, 'payments', 'patient_id', 'amount_service_id')
            ->withPivot('amount_paid', 'payment_status');
    }
}
