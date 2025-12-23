<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:webhook
                            {action=set : Action: set, remove, info}
                            {--url= : Custom webhook URL (default: auto-detect)}';
    // php artisan telegram:webhook set --url=https://nonprudent-ratty-suzette.ngrok-free.dev/task-1/telegram/webhook
    protected $description = 'Manage Telegram bot webhook (test webhook local ngrok)';

    public function handle(TelegramService $telegramService): int
    {
        $action = $this->argument('action');

        return match($action) {
            'set' => $this->setWebhook($telegramService),
            'remove' => $this->removeWebhook($telegramService),
            'info' => $this->getWebhookInfo($telegramService),
            default => $this->error("Unknown action: {$action}") ?? 1,
        };
    }

    protected function setWebhook(TelegramService $telegramService): int
    {
        $url = $this->option('url') ?? route('telegram.webhook');

        $this->info("Setting webhook to: {$url}");

        $result = $telegramService->setWebhook($url);

        if ($result['success']) {
            $this->info($result['message']);
            return 0;
        }

        $this->error( $result['message']);
        return 1;
    }

    protected function removeWebhook(TelegramService $telegramService): int
    {
        $result = $telegramService->removeWebhook();

        if ($result['success']) {
            $this->info($result['message']);
            return 0;
        }

        $this->error($result['message']);
        return 1;
    }

    protected function getWebhookInfo(TelegramService $telegramService): int
    {
        $result = $telegramService->getWebhookInfo();

        if ($result['success']) {
            $info = $result['data'];

            $this->info('Webhook Info:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['URL', $info->getUrl() ?: '(not set)'],
                    ['Has Custom Certificate', $info->hasCustomCertificate() ? 'Yes' : 'No'],
                    ['Pending Update Count', $info->getPendingUpdateCount()],
                    ['Last Error Date', $info->getLastErrorDate() ?: 'N/A'],
                    ['Last Error Message', $info->getLastErrorMessage() ?: 'N/A'],
                    ['Max Connections', $info->getMaxConnections() ?: 'Default'],
                ]
            );
            return 0;
        }

        $this->error( $result['message']);
        return 1;
    }
}
