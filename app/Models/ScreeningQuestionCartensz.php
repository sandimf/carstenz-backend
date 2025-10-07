<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreeningQuestionCartensz extends Model
{
    use HasFactory;

    protected $table = 'screening_question_cartensz';

    protected $fillable = [
        'section',
        'question_text',
        'answer_type',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function answers()
    {
        return $this->hasMany(ScreeningAnswerCartensz::class, 'question_id');
    }
}
