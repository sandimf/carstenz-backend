<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class MigratePatientsAndScreenings extends Command
{
    protected $signature = 'migrate:patients-screenings 
                            {--chunk=1000 : chunk size for reading old DB} 
                            {--dry-run : do not write to new DB, export transform to storage} 
                            {--preserve-ids=1 : keep old IDs (1=true) or generate new IDs (0=false)}';

    protected $description = 'Migrate patients (and create screenings) from old_mysql -> current DB';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');
        $preserveIds = (bool) ((int) $this->option('preserve-ids'));

        $this->info("Start migration: chunk={$chunkSize} dry-run=".($dryRun ? 'yes' : 'no').' preserve-ids='.($preserveIds ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        $exportPatients = [];
        $exportScreenings = [];
        $totalPatients = 0;
        $totalScreenings = 0;
        $mapping = []; // old_id => new_id (used when preserveIds=false)

        $old->table('patients')
            ->orderBy('id')
            ->chunk($chunkSize, function ($rows) use (&$exportPatients, &$exportScreenings, &$totalPatients, &$totalScreenings, &$mapping, $dryRun, $preserveIds) {

                $patientInserts = [];
                $screeningInserts = [];

                foreach ($rows as $row) {
                    // --- Transform patient fields ---
                    // Parse date_of_birth (string -> date)
                    $dob = null;
                    if (! empty($row->date_of_birth)) {
                        try {
                            $dob = Carbon::parse($row->date_of_birth)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $formats = ['d/m/Y', 'd-m-Y', 'm/d/Y', 'Y-m-d H:i:s', 'Y/m/d', 'd.m.Y'];
                            foreach ($formats as $f) {
                                try {
                                    $dob = Carbon::createFromFormat($f, $row->date_of_birth)->format('Y-m-d');
                                    break;
                                } catch (\Exception $_) {
                                }
                            }
                        }
                    }
                    if ($dob === null && ! empty($row->age)) {
                        try {
                            $dob = Carbon::now()->subYears((int) $row->age)->format('Y-m-d');
                        } catch (\Exception $_) {
                            $dob = null;
                        }
                    }
                    if ($dob === null) {
                        $dob = '1970-01-01'; // placeholder, adjust if diperlukan
                    }

                    // gender mapping: 'laki-laki' => 'laki-laki', others => 'perempuan'
                    $genderRaw = strtolower(trim((string) ($row->gender ?? '')));
                    $gender = ($genderRaw === 'laki-laki') ? 'laki-laki' : 'perempuan';

                    // defaults for NOT NULL in new schema
                    $patientData = [
                        // when preserveIds === true we will include 'id' below
                        'uuid' => $row->uuid ?? Str::uuid()->toString(),
                        'user_id' => $row->user_id,
                        'nik' => $row->nik ?? '',
                        'name' => $row->name ?? '',
                        'place_of_birth' => $row->place_of_birth ?? '',
                        'date_of_birth' => $dob,
                        'rt_rw' => $row->rt_rw ?? '',
                        'address' => $row->address ?? '',
                        'village' => $row->village ?? '',
                        'district' => $row->district ?? '',
                        'religion' => $row->religion ?? '',
                        'marital_status' => $row->marital_status ?? '',
                        'occupation' => $row->occupation ?? '',
                        'nationality' => $row->nationality ?? 'WNI',
                        'gender' => $gender,
                        'email' => $row->email ?? '',
                        'blood_type' => $row->blood_type ?? 'unknown',
                        'age' => is_null($row->age) ? 0 : (int) $row->age,
                        'contact' => $row->contact ?? '',
                        'ktp_images' => $row->ktp_images ?? null,
                        'tinggi_badan' => is_null($row->tinggi_badan) ? 0.00 : (float) $row->tinggi_badan,
                        'berat_badan' => is_null($row->berat_badan) ? 0.00 : (float) $row->berat_badan,
                        'archived_at' => $row->archived_at ?? null,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ];

                    if ($preserveIds) {
                        $patientData['id'] = $row->id;
                    }

                    // build screening row if old had any screening-related info
                    $hasScreeningData = (
                        ! is_null($row->screening_status) ||
                        ! is_null($row->health_status) ||
                        ! is_null($row->health_check_status) ||
                        ! is_null($row->payment_status) ||
                        ! is_null($row->konsultasi_dokter) ||
                        ! is_null($row->konsultasi_dokter_status) ||
                        ! is_null($row->pendampingan) ||
                        ! is_null($row->queue) ||
                        ! is_null($row->screening_date)
                    );

                    // screening defaults / mapping to match new schema requirements
                    if ($hasScreeningData) {
                        $screen = [
                            'uuid' => Str::uuid()->toString(),
                            // patient_id assigned later (if preserveIds true -> use old id; else use mapping)
                            'patient_id' => $row->id,
                            'screening_status' => $row->screening_status ?? 'pending',
                            'health_status' => $row->health_status ?? 'pending',
                            'health_check_status' => $row->health_check_status ?? 'pending',
                            'payment_status' => $row->payment_status ?? 'pending',
                            'konsultasi_dokter' => is_null($row->konsultasi_dokter) ? null : (bool) $row->konsultasi_dokter,
                            'konsultasi_dokter_status' => is_null($row->konsultasi_dokter_status) ? null : (bool) $row->konsultasi_dokter_status,
                            'pendampingan' => $row->pendampingan ?? null,
                            'queue' => $row->queue ?? null,
                            'screening_date' => $row->screening_date ?? null,
                            'created_at' => $row->created_at ?? now(),
                            'updated_at' => $row->updated_at ?? now(),
                        ];
                    } else {
                        $screen = null;
                    }

                    // push to arrays for insertion/export
                    $patientInserts[] = $patientData;
                    if ($screen) {
                        $screeningInserts[] = $screen;
                    }

                    // Keep small memory footprint — flush if array grows big (but chunk already limits)
                } // foreach rows

                // If dry-run: just append transform to export arrays
                if ($dryRun) {
                    $exportPatients = array_merge($exportPatients, $patientInserts);
                    // We must set patient_id for screenings properly in dry-run:
                    foreach ($screeningInserts as $s) {
                        $exportScreenings[] = $s;
                    }
                    $this->info('Prepared chunk (dry-run): patients='.count($patientInserts).' screenings='.count($screeningInserts));
                    $GLOBALS['__migr_total_patients'] = ($GLOBALS['__migr_total_patients'] ?? 0) + count($patientInserts);
                    $GLOBALS['__migr_total_screenings'] = ($GLOBALS['__migr_total_screenings'] ?? 0) + count($screeningInserts);

                    return;
                }

                // === Actual insert to new DB ===
                try {
                    if ($preserveIds) {
                        // disable FK checks to allow inserting with explicit IDs (fast)
                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        foreach (array_chunk($patientInserts, 500) as $chunk) {
                            DB::table('patients')->insertOrIgnore($chunk);
                        }
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                        // screenings: patient_id points to same old id (preserved)
                        if (! empty($screeningInserts)) {
                            foreach (array_chunk($screeningInserts, 200) as $sChunk) {
                                // ensure patient_id exists in new DB; insertOrIgnore by uuid to avoid duplicates
                                DB::table('screenings')->insertOrIgnore($sChunk);
                            }
                        }

                        $this->info('Inserted chunk (preserve ids): patients='.count($patientInserts).' screenings='.count($screeningInserts));
                        $totalPatients += count($patientInserts);
                        $totalScreenings += count($screeningInserts);
                    } else {
                        // preserveIds = false: insert patients and collect mapping
                        foreach ($patientInserts as $p) {
                            $pCopy = $p;
                            unset($pCopy['id']); // ensure DB generates new id
                            $newId = DB::table('patients')->insertGetId($pCopy);
                            $oldId = $p['id'] ?? null;
                            if ($oldId) {
                                $mapping[$oldId] = $newId;
                            }
                        }

                        // now insert screenings using mapping
                        foreach ($screeningInserts as $s) {
                            $oldPid = $s['patient_id'];
                            $s['patient_id'] = $mapping[$oldPid] ?? null;
                            if (empty($s['patient_id'])) {
                                // skip screenings if mapping not found
                                continue;
                            }
                            // insert
                            DB::table('screenings')->insertOrIgnore($s);
                        }

                        $this->info('Inserted chunk (new ids): patients='.count($patientInserts).' screenings='.count($screeningInserts));
                        $totalPatients += count($patientInserts);
                        $totalScreenings += count($screeningInserts);
                    }
                } catch (Throwable $e) {
                    // log error and continue (you may want to abort instead)
                    $this->error('Error inserting chunk: '.$e->getMessage());
                    // Re-throw if you want to stop entirely:
                    // throw $e;
                }
            }); // chunk

        // After all chunks
        if ($dryRun) {
            // write exports to storage for review
            $pathP = storage_path('app/migrations/dry_run_patients.json');
            $pathS = storage_path('app/migrations/dry_run_screenings.json');
            if (! is_dir(dirname($pathP))) {
                @mkdir(dirname($pathP), 0755, true);
            }
            file_put_contents($pathP, json_encode($exportPatients, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($pathS, json_encode($exportScreenings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('Dry-run export written to:');
            $this->info(" - {$pathP} (patients) => ".count($exportPatients).' rows');
            $this->info(" - {$pathS} (screenings) => ".count($exportScreenings).' rows');
        } else {
            $this->info("Migration complete. Totals: patients={$totalPatients}, screenings={$totalScreenings}");
            if (! empty($mapping)) {
                $mapPath = storage_path('app/migrations/patient_id_map.json');
                file_put_contents($mapPath, json_encode($mapping, JSON_PRETTY_PRINT));
                $this->info("ID mapping written to {$mapPath} (old_id => new_id)");
            }
        }

        $this->info('Done.');

        return 0;
    }
}
