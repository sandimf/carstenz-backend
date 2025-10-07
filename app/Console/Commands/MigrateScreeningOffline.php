<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateScreeningOffline extends Command
{
    protected $signature = 'migrate:screening-offline {--chunk=200} {--dry-run}';

    protected $description = 'Migrate screening_offline_questions/answers to new screening_questions/answers';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Migrating screening_offline → screening_questions/answers, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        // Migrasi questions
        $old->table('screening_offline_questions')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun) {
            $questions = [];
            foreach ($rows as $row) {
                $questions[] = [
                    'id' => $row->id,
                    'question_text' => $row->question_text,
                    'answer_type' => $row->answer_type,
                    'condition_value' => $row->condition_value,
                    'requires_doctor' => $row->requires_doctor,
                    'options' => $row->options,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if ($dryRun) {
                $this->info('Prepared questions chunk: '.count($questions));

                return;
            }

            DB::table('screening_questions')->insertOrIgnore($questions);
        });

        // Migrasi answers
        $old->table('screening_offline_answers')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun) {
            $answers = [];
            foreach ($rows as $row) {
                $answers[] = [
                    'id' => $row->id,
                    'question_id' => $row->question_id,
                    'patient_id' => $row->patient_id,
                    'answer_text' => $row->answer_text,
                    'queue' => $row->queue,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if ($dryRun) {
                $this->info('Prepared answers chunk: '.count($answers));

                return;
            }

            DB::table('screening_answers')->insertOrIgnore($answers);
        });

        $this->info('Migration complete.');
    }
}
