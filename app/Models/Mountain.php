<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mountain extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function screeningQuestions()
    {
        return $this->hasMany(ScreeningQuestion::class);
    }
}
