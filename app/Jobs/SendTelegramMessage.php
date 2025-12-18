<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $chatId,
        protected string $message,
        protected ?string $replyMarkupJson = null,
        protected string $parseMode = 'HTML'
    ) {}

    public function handle(): void
    {
        $service = app(TelegramService::class);
        $result = $service->sendMessageWithMarkup(
            $this->chatId,
            $this->message,
            $this->parseMode,
            $this->replyMarkupJson
        );

        if (!$result['success']) {
            Log::warning('Telegram job failed', [
                'chat_id' => $this->chatId,
                'message' => $result['message'],
            ]);

            throw new \RuntimeException($result['message']);
        }
    }
}

