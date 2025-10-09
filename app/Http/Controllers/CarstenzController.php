<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PatientCartensz;
use App\Models\ScreeningCartensz;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\ScreeningAnswerCartensz;
use App\Models\ScreeningQuestionCartensz;
use App\Services\Telegram\TelegramService;
use App\Http\Requests\ScreeningCarstenszRequest;
use App\Http\Resources\Screening\QuestionnaireResource;
use App\Models\ClinicServices\Screening\ScreeningAnswer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CarstenzController extends Controller
{

    public function index(){
        $questions = ScreeningQuestionCartensz::all();

        return QuestionnaireResource::collection($questions);
    }

    public function store(ScreeningCarstenszRequest $request)
    {
        $validated = $request->validated();
        $answersRaw = $validated['answers'];
        $answers = is_string($answersRaw) ? json_decode($answersRaw, true) : $answersRaw;
        if (! is_array($answers)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format jawaban tidak valid',
            ], 422);
        }

        // Buat user
        $user = User::create([
            'email' => $validated['email'],
            'name'  => $validated['name'] ?? 'Guest',
            'password' => Hash::make(Str::random(12)),
            'email_verified_at' => now(),
        ]);

        $today = now()->toDateString();
        $lastQueue = ScreeningCartensz::whereDate('screening_date', $today)->max('queue');
        $newQueueNumber = $lastQueue ? $lastQueue + 1 : 1;

        $patient = PatientCartensz::create([
            'uuid'            => Str::uuid(),
            'name'            => $validated['name'],
            'date_of_birth'   => $validated['date_of_birth'],
            'gender'          => $validated['gender'] ?? null,
            'nationality'     => $validated['nationality'] ?? null,
            'passport_number' => $validated['passport_number'],
            'email'           => $validated['email'],
            'contact'         => $validated['contact'] ?? null,
            'passport_images' => $validated['passport_images'] ?? null,
        ]);

        $screening = ScreeningCartensz::create([
            'uuid'                => Str::uuid(),
            'patient_cartensz_id' => $patient->id,
            'screening_status'    => 'pending',
            'health_status'       => 'pending',
            'health_check_status' => 'pending',
            'queue'               => $newQueueNumber,
            'screening_date'      => $today,
        ]);

        foreach ($answers as $answer) {
            ScreeningAnswerCartensz::create([
                'patient_id'  => $patient->id, 
                'question_id' => $answer['questioner_id'],
                'answer_text' => is_array($answer['answer'])
                    ? json_encode($answer['answer'])
                    : $answer['answer'],
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Screening has been successfully saved.',
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'patient' => [
                    'id'    => $patient->id,
                    'name'  => $patient->name,
                    'email' => $patient->email,
                    'uuid'  => $patient->uuid,
                ],
                'screening' => [
                    'id'    => $screening->id,
                    'uuid'  => $screening->uuid,
                    'queue' => $screening->queue,
                    'date'  => $screening->screening_date,
                ],
            ],
        ], 201);
    }


    public function listScreenings(Request $request)
    {
        $search = $request->input('search');

        $query = PatientCartensz::with(['answers', 'screeningCartensz'])
            ->orderBy('created_at', 'desc');


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('passport_number', 'like', "%{$search}%");
            });
        }

        $patients = $query->get();

        $data = $patients->map(function ($patient) {
            return [
                'id'                => $patient->id,
                'uuid'              => $patient->uuid,
                'name'              => $patient->name,
                'email'             => $patient->email,
                'contact'           => $patient->contact,
                'passport_number'   => $patient->passport_number,
                'screening_status'  => $patient->screeningCartensz->screening_status ?? 'pending',
                'queue'             => $patient->screeningCartensz->queue ?? null,
                'screening_date'    => $patient->screeningCartensz->screening_date ?? null,
                'answers'           => $patient->answers->map(function ($answer) {
                    return [
                        'question_id' => $answer->question_id,
                        'answer'      => $answer->answer_text,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function getScreeningDetail($uuid)
    {
        $patient = PatientCartensz::with(['answers', 'screeningCartensz', 'answers.question'])
            ->where('uuid', $uuid)
            ->first();

        if (! $patient) {
            return response()->json([
                'status' => 'error',
                'message' => 'Screening data not found',
            ], 404);
        }

        $screening = $patient->screeningCartensz;

        return response()->json([
            'status' => 'success',
            'data' => [
                'id'               => $patient->id,
                'uuid'             => $patient->uuid,
                'name'             => $patient->name,
                'email'            => $patient->email,
                'contact'          => $patient->contact,
                'passport_number'  => $patient->passport_number,
                'date_of_birth'    => $patient->date_of_birth,
                'gender'           => $patient->gender,
                'nationality'      => $patient->nationality,
                'screening' => $screening ? [
                    'id'                  => $screening->id,
                    'uuid'                => $screening->uuid,
                    'screening_status'    => $screening->screening_status,
                    'health_status'       => $screening->health_status,
                    'health_check_status' => $screening->health_check_status,
                    'queue'               => $screening->queue,
                    'screening_date'      => $screening->screening_date,
                ] : null,
                'answers' => $patient->answers->map(function ($answer) {
                    return [
                        'question_id'   => $answer->question_id,
                        'question_text' => $answer->question->question_text ?? null,
                        'answer'        => $answer->answer_text,
                    ];
                }),
            ],
        ]);
    }

    public function exportScreeningsCsv(Request $request)
{
    $search = $request->input('search');

    $query = PatientCartensz::with(['answers', 'screeningCartensz'])
        ->orderBy('created_at', 'desc');

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('passport_number', 'like', "%{$search}%");
        });
    }

    $patients = $query->get();

    // Ambil semua pertanyaan agar header CSV lengkap
    $questions = ScreeningQuestionCartensz::all();

    $response = new StreamedResponse(function() use ($patients, $questions) {
        $handle = fopen('php://output', 'w');

        // Header CSV
        $header = [
            'Name',
            'Email',
            'Contact',
            'Passport Number',
            'Screening Date',
        ];

        // Tambahkan kolom untuk setiap pertanyaan
        foreach ($questions as $question) {
            $header[] = $question->question_text;
        }

        fputcsv($handle, $header);

        foreach ($patients as $patient) {
            $row = [
                $patient->name,
                $patient->email,
                $patient->contact,
                $patient->passport_number,
                $patient->screeningCartensz->screening_date ?? null,
            ];

            // Map jawaban ke setiap pertanyaan
            $answersMap = $patient->answers->keyBy('question_id');

            foreach ($questions as $question) {
                $row[] = $answersMap[$question->id]->answer_text ?? '';
            }

            fputcsv($handle, $row);
        }

        fclose($handle);
    });

    $filename = 'screenings_' . now()->format('Ymd_His') . '.csv';
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

    return $response;
}

}
