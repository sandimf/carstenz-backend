<?php

namespace App\Console\Commands;

use App\Models\Payment\AmountService;
use App\Models\Payment\Payment;
use App\Models\Payment\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigratePaymentsAll extends Command
{
    protected $signature = 'migrate:payments:all {--chunk=200} {--dry-run}';

    protected $description = 'Migrate payments from old DB to new schema with transactions';

    public function handle()
    {
        $chunk = (int) $this->option('chunk');
        $dryRun = $this->option('dry-run');

        // koneksi db lama (pastikan ada di config/database.php dengan key `mysql_old`)
        $oldPayments = DB::connection('old_mysql')->table('payments');

        $count = $oldPayments->count();
        $this->info("Found {$count} old payments.");

        $oldPayments->orderBy('id')->chunk($chunk, function ($rows) use ($dryRun) {
            foreach ($rows as $row) {
                // cek service type
                $amountService = AmountService::firstOrCreate(
                    ['type' => $row->service_types ?? 'unknown'],
                    ['amount' => $row->amount_paid ?? 0]
                );

                if ($dryRun) {
                    $this->line("Would migrate payment_id={$row->id}, patient_id={$row->patient_id}, amount={$row->amount_paid}");

                    continue;
                }

                // simpan ke payments baru
                $payment = Payment::updateOrCreate(
                    ['uuid' => $row->uuid ?? (string) Str::uuid()],
                    [
                        'no_transaction' => $row->no_transaction ?? strtoupper(Str::random(10)),
                        'patient_id' => $row->patient_id,
                        'cashier_id' => $row->cashier_id ?? null,
                        'payment_status' => $row->payment_status == 1, // boolean
                        'amount_paid' => $row->amount_paid,
                        'payment_method' => $row->payment_method,
                        'payment_proof' => $row->payment_proof,
                        'service_types' => $row->service_types,
                        'amount_service_id' => $amountService->id,
                    ]
                );

                // simpan pivot
                $payment->amountServices()->syncWithoutDetaching([$amountService->id]);

                // generate transactions baru
                Transaction::updateOrCreate(
                    ['uuid' => $row->uuid ?? (string) Str::uuid()],
                    [
                        'transaction_number' => $row->no_transaction ?? strtoupper(Str::random(10)),
                        'patient_id' => $row->patient_id ?? null,
                        'type' => $row->service_types ?? 'general',
                        'amount_paid' => $row->amount_paid,
                        'payment_method' => $row->payment_method ?? 'cash',
                        'payment_proof' => $row->payment_proof,
                        'status' => $row->payment_status == 1 ? 'paid' : 'unpaid',
                    ]
                );
            }
        });

        $this->info('✅ Migration finished.');
    }
}
