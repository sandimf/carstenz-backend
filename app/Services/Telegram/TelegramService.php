<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;

    protected $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function sendMessage(string $message): void
    {
        $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $message,
        ]);

        Log::info('Telegram sendMessage response: '.$response->body());
    }

    public function sendDocument(string $filePath, ?string $caption = null): void
    {
        if (! file_exists($filePath)) {
            throw new \Exception("File {$filePath} tidak ditemukan!");
        }

        $response = Http::attach(
            'document',
            file_get_contents($filePath),
            basename($filePath)
        )->post("https://api.telegram.org/bot{$this->botToken}/sendDocument", [
            'chat_id' => $this->chatId,
            'caption' => $caption ?? '',
        ]);

        Log::info('Telegram sendDocument response: '.$response->body());

        if (! $response->successful()) {
            throw new \Exception('Gagal kirim file ke Telegram. Response: '.$response->body());
        }
    }
}
