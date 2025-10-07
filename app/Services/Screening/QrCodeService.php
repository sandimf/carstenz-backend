<?php

namespace App\Services\Screening;

use App\Models\Users\Patient;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Generate QR code for patient with unique URL
     */
    public function generatePatientQrCode(Patient $patient)
    {
        try {
            // Generate unique link and verification token
            $uniqueLink = $this->generateUniqueLink();
            $verificationToken = $this->generateVerificationToken();

            // Create the unique URL that will contain patient data
            $patientUrl = url("/api/v1/tb0xPxDOVTRmhejyE3Wukn4QBni/patient/{$uniqueLink}/H0u01BshX7nsyxm0Qkeqa8N9odxvmAzC");

            // Generate filename based on patient info
            $filename = $this->generateQrFilename($patient);

            // Call external QR code API to generate QR code
            $qrResponse = $this->callQrCodeApi($patientUrl, $filename);

            if ($qrResponse['success']) {
                // Download and save QR code image to Laravel storage
                $localPath = $this->saveQrCodeToLaravel($qrResponse, $filename);

                // Update patient record with QR code info
                $patient->update([
                    'qr_code_path' => $localPath,
                    'unique_link' => $uniqueLink,
                    'verification_token' => $verificationToken,
                ]);

                Log::info('QR Code generated and saved successfully for patient: '.$patient->id, [
                    'local_path' => $localPath,
                    'unique_link' => $uniqueLink,
                ]);

                return [
                    'success' => true,
                    'qr_code_path' => $localPath,
                    'unique_link' => $uniqueLink,
                    'patient_url' => $patientUrl,
                    'filename' => $filename.'.png',
                    'full_local_path' => storage_path('app/public/qr-codes/'.$filename.'.png'),
                ];
            }

            throw new \Exception('Failed to generate QR code: '.($qrResponse['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('QR Code generation failed for patient '.$patient->id.': '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to generate QR code: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Generate unique link identifier (longer for better security)
     */
    private function generateUniqueLink()
    {
        do {
            // Generate longer unique link (64 characters) for better security
            $uniqueLink = Str::random(64);
        } while (Patient::where('unique_link', $uniqueLink)->exists());

        return $uniqueLink;
    }

    /**
     * Generate verification token
     */
    private function generateVerificationToken()
    {
        return hash('sha256', Str::random(64).time());
    }

    /**
     * Generate QR code filename based on patient info
     */
    private function generateQrFilename(Patient $patient)
    {
        $patientName = Str::slug($patient->name);
        $timestamp = now()->format('Ymd_His');

        return "qr_patient_{$patient->id}_{$patientName}_{$timestamp}";
    }

    /**
     * Call external QR code generation API
     */
    private function callQrCodeApi($url, $filename)
    {
        try {
            // Get QR code API endpoint from config or use default
            $qrApiUrl = config('app.qr_api_url', 'http://localhost:3001/api/qr');

            $response = Http::timeout(30)->post($qrApiUrl, [
                'url' => $url,
                'filename' => $filename,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('QR API returned error: '.$response->body());
        } catch (\Exception $e) {
            Log::error('QR API call failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Get patient data by unique link
     */
    public function getPatientDataByLink($uniqueLink)
    {
        $patient = Patient::where('unique_link', $uniqueLink)
            ->with(['physicalExaminations.paramedis', 'user'])
            ->first();

        if (! $patient) {
            return null;
        }

        // Return sanitized patient data for API response
        return [
            'Nama Lengkap' => $patient->name,
            'Nik' => $patient->nik,
            'Umur' => $patient->age,
            'Jenis Kelamin' => $patient->gender,
            'Nomor Telepon' => $patient->contact,
            'Alamat' => $patient->address,
            'Status Kesehatan' => $patient->health_status,
            'Tanggal Screening' => $patient->screening_date,
            'Pemeriksaan Fisik' => $patient->physicalExaminations->map(function ($exam) {
                return [
                    'examination_date' => $exam->created_at,
                    'paramedis_name' => $exam->paramedis->name ?? 'N/A',
                    'blood_pressure' => $exam->blood_pressure,
                    'heart_rate' => $exam->heart_rate,
                    'oxygen_saturation' => $exam->oxygen_saturation,
                    'respiratory_rate' => $exam->respiratory_rate,
                    'body_temperature' => $exam->body_temperature,
                    'physical_assessment' => $exam->physical_assessment,
                    'health_status' => $exam->health_status,
                    'medical_advice' => $exam->medical_advice,
                ];
            }),
            // 'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Download QR code from external API and save to Laravel storage
     */
    private function saveQrCodeToLaravel($qrResponse, $filename)
    {
        try {
            // Create qr-codes directory if it doesn't exist
            $qrDirectory = storage_path('app/public/qr-codes');
            if (! File::exists($qrDirectory)) {
                File::makeDirectory($qrDirectory, 0755, true);
            }

            // Get the external QR code image URL or path
            $externalImagePath = $qrResponse['data']['fullPath'] ?? null;

            if ($externalImagePath && File::exists($externalImagePath)) {
                // Copy file from external API storage to Laravel storage
                $localFilename = $filename.'.png';
                $localPath = 'qr-codes/'.$localFilename;
                $fullLocalPath = storage_path('app/public/'.$localPath);

                // Copy the file
                File::copy($externalImagePath, $fullLocalPath);

                Log::info('QR Code image copied to Laravel storage', [
                    'from' => $externalImagePath,
                    'to' => $fullLocalPath,
                ]);

                return '/storage/'.$localPath;
            } else {
                // If external file doesn't exist, try to download via HTTP
                return $this->downloadQrCodeViaHttp($qrResponse, $filename);
            }

        } catch (\Exception $e) {
            Log::error('Failed to save QR code to Laravel storage: '.$e->getMessage());

            // Fallback: return external path
            return $qrResponse['data']['qrImagePath'] ?? '/images/qr-fallback.png';
        }
    }

    /**
     * Download QR code via HTTP if file copy fails
     */
    private function downloadQrCodeViaHttp($qrResponse, $filename)
    {
        try {
            // Try to download the image via HTTP
            $imageUrl = url($qrResponse['data']['qrImagePath']);
            $response = Http::timeout(10)->get($imageUrl);

            if ($response->successful()) {
                $localFilename = $filename.'.png';
                $localPath = 'qr-codes/'.$localFilename;

                // Save to Laravel storage
                Storage::disk('public')->put($localPath, $response->body());

                Log::info('QR Code downloaded via HTTP to Laravel storage', [
                    'url' => $imageUrl,
                    'local_path' => $localPath,
                ]);

                return '/storage/'.$localPath;
            }

            throw new \Exception('HTTP download failed');
        } catch (\Exception $e) {
            Log::error('Failed to download QR code via HTTP: '.$e->getMessage());

            // Return external path as fallback
            return $qrResponse['data']['qrImagePath'] ?? '/images/qr-fallback.png';
        }
    }
}
