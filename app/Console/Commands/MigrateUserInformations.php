<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateUserInformations extends Command
{
    protected $signature = 'migrate:user-informations {--chunk=200} {--dry-run}';

    protected $description = 'Migrate user_data (old) to user_informations (new)';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Migrating user_data → user_informations, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        $old->table('user_data')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun) {
            $records = [];

            foreach ($rows as $row) {
                $records[] = [
                    'patient_id' => null, // default null karena di table lama tidak ada
                    'session_id' => $row->session_id,
                    'device_type' => $row->device_type,
                    'device_model' => $row->device_model,
                    'os_version' => $row->os_version,
                    'browser' => $row->browser,
                    'browser_version' => $row->browser_version,
                    'ip_address' => $row->ip_address,
                    'user_agent' => $row->user_agent,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if ($dryRun) {
                $this->info('Prepared user_informations chunk: '.count($records));

                return;
            }

            if (! empty($records)) {
                DB::table('user_informations')->insertOrIgnore($records);
                $this->info('Inserted user_informations chunk: '.count($records));
            }
        });

        $this->info('Migration user_data → user_informations complete.');
    }
}
