<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateMedicalRecords extends Command
{
    protected $signature = 'migrate:medical-records {--chunk=200} {--dry-run}';

    protected $description = 'Migrate old medical_records to new medical_records table';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Migrating medical_records, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        // Koneksi ke database lama
        $old = DB::connection('old_mysql');

        $old->table('medical_records')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun) {
            $records = [];

            foreach ($rows as $row) {
                // Pastikan physical_examination_id lama sudah ada di tabel baru
                $newPhysicalExam = DB::table('physical_examinations')->where('id', $row->physical_examination_id)->first();
                if (! $newPhysicalExam) {
                    $this->warn("Physical examination ID {$row->physical_examination_id} not found, skipping record ID {$row->id}");

                    continue;
                }

                $records[] = [
                    'uuid' => Str::uuid()->toString(),
                    'patient_id' => $row->patient_id,
                    'physical_examination_id' => $row->physical_examination_id,
                    'medical_record_number' => $row->medical_record_number,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if ($dryRun) {
                $this->info('Prepared medical_records chunk: '.count($records));

                return;
            }

            if (! empty($records)) {
                DB::table('medical_records')->insertOrIgnore($records);
                $this->info('Inserted medical_records chunk: '.count($records));
            }
        });

        $this->info('Medical records migration complete.');
    }
}
