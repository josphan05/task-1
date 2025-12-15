<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

class TelegramService
{
    protected Api $telegram;

    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
        $guzzle = new Client([
            'verify' => false,
            'timeout' => 30,
        ]);

        $this->telegram = new Api(
            config('telegram.bots.mybot.token'),
            false,
            new GuzzleHttpClient($guzzle)
        );
    }


    public function sendMessage(string $chatId, string $message, string $parseMode = 'HTML'): array
    {
        Log::info('Telegram sendMessage', [
            'chat_id' => $chatId,
            'message_length' => strlen($message),
        ]);

        try {
            $response = $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            Log::info('Telegram message sent successfully', ['response' => $response]);

            return [
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công!',
                'data' => $response,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Telegram sendMessage error', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'message' => $this->parseErrorMessage($e->getMessage()),
                'data' => null,
            ];
        }
    }


    public function sendMessageToUsers(array $userIds, string $message): array
    {
        $sent = 0;
        $failed = 0;
        $errors = [];

        $users = $this->userRepository
            ->findWhereIn('id', $userIds)
            ->filter(function ($user) {
                return !empty($user->telegram_id);
            });

        if ($users->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Không có người dùng nào có Telegram ID.',
                'sent' => 0,
                'failed' => 0,
            ];
        }

        foreach ($users as $user) {
            $result = $this->sendMessage($user->telegram_id, $message);

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
                $errors[] = "{$user->name}: {$result['message']}";
            }

            usleep(100000);
        }

        return [
            'success' => $sent > 0,
            'message' => "Đã gửi: {$sent}, Thất bại: {$failed}",
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }


    protected function parseErrorMessage(string $error): string
    {
        if (str_contains($error, 'chat not found')) {
            return 'Chat không tồn tại. User cần chat với bot trước.';
        }

        if (str_contains($error, 'bot was blocked')) {
            return 'User đã block bot.';
        }

        if (str_contains($error, 'user is deactivated')) {
            return 'Tài khoản Telegram đã bị vô hiệu hóa.';
        }

        if (str_contains($error, 'Too Many Requests')) {
            return 'Gửi quá nhiều tin nhắn. Vui lòng thử lại sau.';
        }

        return $error;
    }
}
