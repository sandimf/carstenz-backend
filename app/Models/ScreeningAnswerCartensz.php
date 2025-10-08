<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningAnswerCartensz extends Model
{
    use HasFactory;

    protected $table = 'screening_answer_cartensz';

    protected $fillable = [
        'question_id',
        'patient_id',
        'answer_text',
        'queue',
    ];

    public function question()
    {
        return $this->belongsTo(ScreeningQuestionCartensz::class, 'question_id');
    }

    public function patient()
    {
        return $this->belongsTo(PatientCartensz::class, 'patient_id');
    }

}
