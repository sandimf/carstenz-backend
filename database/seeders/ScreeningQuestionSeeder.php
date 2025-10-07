<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScreeningQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $questions = [
            [
                'section' => 'B. Medical History (tick all that apply)',
                'question_text' => 'Cardiovascular (tick all that apply)',
                'answer_type'   => 'checkbox',
                'options'       => json_encode(['Hypertension','Heart disease','Chest pain','Fainting','Arrhythmia']),
            ],
            [
                'question_text' => 'Respiratory',
                'answer_type'   => 'checkbox',
                'options'       => json_encode(['Asthma','COPD/Lung disease','Prior HAPE (High-Altitude Pulmonary Edema)']),
            ],
            [
                'question_text' => 'Neurological',
                'answer_type'   => 'checkbox',
                'options'       => json_encode(['Seizure','Stroke history','Migraine']),
            ],
            [
                'question_text' => 'Endocrine & Metabolic',
                'answer_type'   => 'checkbox',
                'options'       => json_encode(['Diabetes','Thyroid disorder']),
            ],
            [
                'question_text' => 'Musculoskeletal',
                'answer_type'   => 'checkbox',
                'options'       => json_encode(['Joint injury/surgery','Back problems']),
            ],
            [
                'question_text' => 'Psychiatric',
                'answer_type'   => 'checkbox',
                'options'       => json_encode(['Anxiety/panic','Depression']),
            ],
            [
                'question_text' => 'Allergies',
                'answer_type'   => 'checkbox_textarea',
                'options'       => json_encode(['Drug allergies']),
            ],
            [
                'question_text' => 'Surgical history (Year – Type)',
                'answer_type'   => 'textarea',
            ],

            // C. High-Altitude Experience
            [
                'section' => 'C. High‑Altitude Experience',
                'question_text' => 'Highest altitude reached (m)',
                'answer_type'   => 'number',
            ],
            [
                'question_text' => 'History of AMS/HAPE/HACE? (describe)',
                'answer_type'   => 'textarea',
            ],
            [
                'question_text' => 'Treatment & recovery (evacuation/medication/rest)',
                'answer_type'   => 'textarea',
            ],

            // D. Current Medications
            [
                'section' => 'D. Current Medications',
                'question_text' => 'Daily medications (name – dose – frequency)',
                'answer_type'   => 'textarea',
            ],
            [
                'question_text' => 'Self-carry emergency meds (e.g., inhaler, insulin)',
                'answer_type'   => 'textarea',
            ],

            [
                'section' => 'E. Vaccination & General',
                'question_text' => 'Last tetanus (year)',
                'answer_type'   => 'number',
            ],
            [
                'question_text' => 'Other vaccinations (if any)',
                'answer_type'   => 'textarea',
            ],

            [
                'section' => 'F. Lifestyle & Fitness',
                'question_text' => 'Smoking',
                'answer_type'   => 'select',
                'options'       => json_encode(['Yes','No']),
            ],
            [
                'question_text' => 'Alcohol',
                'answer_type'   => 'select',
                'options'       => json_encode(['Yes','No']),
            ],

            
            [
                'section' => 'G. Baseline Vitals  (complete only if known; otherwise we will measure on arrival)',
                'question_text' => 'Resting Heart Rate (bpm)',
                'answer_type'   => 'number',
            ],
            [
                'question_text' => 'Blood Pressure (mmHg)',
                'answer_type'   => 'text',
            ],
            [
                'question_text' => 'SpO₂ at sea level (%)',
                'answer_type'   => 'number',
            ],
            [
                'question_text' => 'Weight (kg)',
                'answer_type'   => 'number',
            ],

            // H. Consent
            [
                'section' => 'H. Consent',
                'question_text' => 'I declare that the information provided is true and complete. I consent to the use of this data by the expedition medical team for clinical decision‑making and emergency response. I understand that care provided on site prioritizes stabilization and safe evacuation when indicated.',
                'answer_type'   => 'checkbox',
                'options'       => json_encode('Cleared to participate'),
            ],
        ];

        foreach ($questions as $q) {
            DB::table('screening_question_cartensz')->insert(array_merge([
                'created_at'      => $now,
                'updated_at'      => $now,
            ], $q));
        }
    }
}
