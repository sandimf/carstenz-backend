<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Nurse extends Model
{
    use HasFactory;

    protected $table = 'nurses';

    protected $fillable = [
        'uuid',
        'user_id',
        'nik',
        'email',
        'name',
        'address',
        'date_of_birth',
        'phone',
        'signature',
        'role',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot method untuk UUID otomatis
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
