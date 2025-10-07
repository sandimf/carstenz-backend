<?php

namespace App\Models;

use App\Models\Users\Patient;
use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    protected $table = 'user_informations';

    protected $fillable = [
        'patient_id',
        'session_id',
        'device_type',
        'device_model',
        'os_version',
        'browser',
        'browser_version',
        'ip_address',
        'user_agent',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
