<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateCashiers extends Command
{
    protected $signature = 'migrate:cashiers {--chunk=200} {--dry-run}';

    protected $description = 'Migrate old cashiers table to new cashiers table';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Migrating cashiers, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        $old->table('cashiers')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun) {
            $records = [];

            foreach ($rows as $row) {
                $records[] = [
                    'uuid' => $row->uuid ?? Str::uuid()->toString(),
                    'user_id' => $row->user_id,
                    'nik' => $row->nik,
                    'email' => $row->email,
                    'name' => $row->name,
                    'address' => $row->address,
                    'date_of_birth' => $row->date_of_birth,
                    'phone' => $row->phone,
                    'signature' => $row->signature,
                    'role' => 'cashier',
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];
            }

            if ($dryRun) {
                $this->info('Prepared cashiers chunk: '.count($records));

                return;
            }

            if (! empty($records)) {
                DB::table('cashiers')->insertOrIgnore($records);
                $this->info('Inserted cashiers chunk: '.count($records));
            }
        });

        $this->info('Cashiers migration complete.');
    }
}
