<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClinicServices\Screening\ScreeningQuestion;
use App\Models\ClinicServices\Screening\ScreeningQuestionTranslation;

class MigrateOldQuestionsToTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        // ID Semeru, bisa diganti sesuai mountain lain
        $mountainId = 1;

        $questions = ScreeningQuestion::all();

        foreach ($questions as $q) {
            // Update mountain_id langsung di tabel utama
            if (!$q->mountain_id) {
                $q->mountain_id = $mountainId;
                $q->save();
            }

            // Buat translation jika question_text ada
            if ($q->question_text) {
                ScreeningQuestionTranslation::updateOrCreate(
                    [
                        'screening_question_id' => $q->id,
                        'locale' => 'id',
                    ],
                    [
                        'question_text' => $q->question_text,
                        'options' => $q->options,
                    ]
                );
            }
        }
    }
}
