<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Backup database, zip file, dan kirim ke Telegram';

    public function handle(TelegramService $telegram)
    {
        $fileName = 'backup_'.now()->format('Y_m_d_H_i_s').'.sql';
        $filePath = storage_path('app/'.$fileName);

        // Konfigurasi database
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        // Backup database
        $command = "mysqldump -h {$dbHost} -u {$dbUser} -p'{$dbPass}' {$dbName} > \"{$filePath}\" 2>&1";
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || ! file_exists($filePath)) {
            $telegram->sendMessage('Backup database GAGAL. Output: '.implode("\n", $output));
            $this->error('Backup gagal! Output: '.implode("\n", $output));

            return;
        }

        // Zip file agar ukuran lebih kecil
        $zipPath = storage_path('app/'.basename($fileName, '.sql').'.zip');
        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            $zip->addFile($filePath, $fileName);
            $zip->close();

            try {
                // Kirim pesan & file zip ke Telegram
                $telegram->sendMessage("Backup database berhasil: {$fileName} (zip)");
                $telegram->sendDocument($zipPath, 'Backup database '.now()->toDateTimeString());

                $this->info('Backup berhasil, zip dibuat, dan dikirim ke Telegram.');

            } catch (\Exception $e) {
                $telegram->sendMessage('Backup berhasil tapi gagal kirim ke Telegram: '.$e->getMessage());
                $this->error('Gagal kirim file ke Telegram: '.$e->getMessage());
            }

            // Hapus file sementara
            unlink($filePath);
            unlink($zipPath);

        } else {
            $telegram->sendMessage('Backup database berhasil tapi gagal buat zip file.');
            $this->error('Gagal buat zip file.');
        }
    }
}
