<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Users\Cashier;
use App\Models\Users\Nurse;
use App\Models\Users\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'role',
        'name',
        'avatar',
        'email_verified_at',
        'email',
        'password',
        'provider',
        'provider_id',
        'provider_token',
        'username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->username)) {
                do {
                    $username = 'user_'.bin2hex(random_bytes(3)); // Contoh: user_a1b2c3
                } while (User::where('username', $username)->exists());

                $user->username = $username;
            }
        });
    }

    public function patient()
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class); // Relasi ke model Patient
    }

    public function nurse()
    {
        return $this->hasMany(nurse::class); // Relasi ke model Patient
    }

    public function cashier()
    {
        return $this->hasMany(Cashier::class); // atau hasOne, tergantung struktur
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
