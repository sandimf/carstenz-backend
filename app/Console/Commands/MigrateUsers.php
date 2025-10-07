<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateUsers extends Command
{
    protected $signature = 'migrate:users {--chunk=200} {--dry-run} {--preserve-ids=1}';

    protected $description = 'Migrate users from old to new DB structure';

    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');
        $preserveIds = (bool) ((int) $this->option('preserve-ids'));

        $this->info("Migrating users, chunk={$chunkSize}, dry-run=".($dryRun ? 'yes' : 'no'));

        $old = DB::connection('old_mysql');

        $old->table('users')->orderBy('id')->chunk($chunkSize, function ($rows) use ($dryRun, $preserveIds) {
            $users = [];

            foreach ($rows as $row) {
                // map role lama → baru
                $roleMap = [
                    'patients' => 'patient',
                    'paramedis' => 'nurse',
                    'admin' => 'admin',
                    'doctor' => 'doctor',
                    'manager' => 'manager',
                    'cashier' => 'cashier',
                    'warehouse' => 'warehouse',
                ];
                $role = $roleMap[$row->role] ?? 'patient';

                $data = [
                    'uuid' => Str::uuid()->toString(),
                    'username' => null,
                    'name' => $row->name,
                    'email' => $row->email,
                    'role' => $role,
                    'email_verified_at' => $row->email_verified_at,
                    'avatar' => $row->avatar ?? 'avatars/avatar.svg',
                    'password' => $row->password,
                    'provider' => $row->provider,
                    'provider_id' => $row->provider_id,
                    'provider_token' => $row->provider_token,
                    'remember_token' => $row->remember_token,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ];

                if ($preserveIds) {
                    $data['id'] = $row->id;
                }

                $users[] = $data;
            }

            if ($dryRun) {
                $this->info('Prepared chunk for dry-run: '.count($users).' rows');

                return;
            }

            try {
                if ($preserveIds) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    foreach (array_chunk($users, 200) as $chunk) {
                        DB::table('users')->insertOrIgnore($chunk);
                    }
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } else {
                    foreach ($users as $u) {
                        DB::table('users')->insertGetId($u);
                    }
                }
                $this->info('Inserted chunk: '.count($users).' rows');
            } catch (\Throwable $e) {
                $this->error('Error inserting chunk: '.$e->getMessage());
            }
        });

        $this->info('Migration complete.');

        return 0;
    }
}
