<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigratePhysicalExaminations extends Command
{
    protected $signature = 'migrate:physical-exams {--chunk=200} {--dry-run} {--preserve-ids=1}';

    protected $description = 'Migrate physical_examinations from old DB to new DB structure';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');
        $preserveIds = (bool) ((int) $this->option('preserve-ids'));

        $this->info("Migrating physical_examinations, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        $old->table('physical_examinations')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun, $preserveIds) {
            $exams = [];

            foreach ($rows as $row) {
                // Mapping paramedis_id lama ke nurse_id baru
                $nurseId = null;
                if ($row->paramedis_id) {
                    $nurse = DB::table('nurses')->where('id', $row->paramedis_id)->first();
                    $nurseId = $nurse->id ?? null;
                }

                $data = [
                    'uuid' => Str::uuid()->toString(),
                    'patient_id' => $row->patient_id,
                    'nurse_id' => $nurseId,
                    'doctor_id' => $row->doctor_id,
                    'blood_pressure' => $row->blood_pressure,
                    'heart_rate' => $row->heart_rate,
                    'oxygen_saturation' => $row->oxygen_saturation,
                    'respiratory_rate' => $row->respiratory_rate,
                    'body_temperature' => $row->body_temperature,
                    'physical_assessment' => $row->physical_assessment,
                    'reason' => $row->reason,
                    'medical_advice' => $row->medical_advice,
                    'health_status' => $row->health_status,
                    'doctor_advice' => $row->doctor_advice,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];

                if ($preserveIds) {
                    $data['id'] = $row->id;
                }

                $exams[] = $data;
            }

            if ($dryRun) {
                $this->info('Prepared chunk for dry-run: '.count($exams).' rows');

                return;
            }

            try {
                if ($preserveIds) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    foreach (array_chunk($exams, 200) as $chunk) {
                        DB::table('physical_examinations')->insertOrIgnore($chunk);
                    }
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } else {
                    foreach ($exams as $e) {
                        DB::table('physical_examinations')->insertGetId($e);
                    }
                }
                $this->info('Inserted chunk: '.count($exams).' rows');
            } catch (\Throwable $e) {
                $this->error('Error inserting chunk: '.$e->getMessage());
            }
        });

        $this->info('Migration complete.');

        return 0;
    }
}
