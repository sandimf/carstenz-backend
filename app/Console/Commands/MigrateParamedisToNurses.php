<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateParamedisToNurses extends Command
{
    protected $signature = 'migrate:paramedis-nurses {--chunk=200} {--dry-run} {--preserve-ids=1}';

    protected $description = 'Migrate paramedis (old) to nurses (new)';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');
        $preserveIds = (bool) ((int) $this->option('preserve-ids'));

        $this->info("Migrating paramedis → nurses, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        $old->table('paramedis')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun, $preserveIds) {
            $nurses = [];

            foreach ($rows as $row) {
                $data = [
                    'uuid' => $row->uuid ?? Str::uuid()->toString(),
                    'user_id' => $row->user_id,
                    'nik' => $row->nik,
                    'email' => $row->email,
                    'name' => $row->name,
                    'address' => $row->address,
                    'date_of_birth' => $row->date_of_birth,
                    'phone' => $row->phone,
                    'signature' => $row->signature,
                    'role' => 'nurse',
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];

                if ($preserveIds) {
                    $data['id'] = $row->id;
                }

                $nurses[] = $data;
            }

            if ($dryRun) {
                $this->info('Prepared chunk for dry-run: '.count($nurses).' rows');

                return;
            }

            try {
                if ($preserveIds) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    foreach (array_chunk($nurses, 200) as $chunk) {
                        DB::table('nurses')->insertOrIgnore($chunk);
                    }
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } else {
                    foreach ($nurses as $n) {
                        $newId = DB::table('nurses')->insertGetId($n);
                    }
                }
                $this->info('Inserted chunk: '.count($nurses).' rows');
            } catch (\Throwable $e) {
                $this->error('Error inserting chunk: '.$e->getMessage());
            }
        });

        $this->info('Migration complete.');

        return 0;
    }
}
