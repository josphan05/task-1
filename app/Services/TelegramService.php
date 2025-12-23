<?php

namespace App\Services;

use App\Jobs\SendTelegramMessage;
use App\Models\User;
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


    public function sendMessageWithMarkup(string $chatId, string $message, string $parseMode = 'HTML', ?string $replyMarkupJson = null): array
    {
        Log::info('Telegram sendMessageWithMarkup', [
            'chat_id' => $chatId,
            'message_length' => strlen($message),
            'has_markup' => !empty($replyMarkupJson),
        ]);
        try {
            // Chỉ escape HTML nếu parse_mode không phải HTML hoặc Markdown
            $cleanMessage = ($parseMode === 'HTML' || $parseMode === 'Markdown' || $parseMode === 'MarkdownV2')
                ? $message
                : htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

            $params = [
                'chat_id' => $chatId,
                'text' => $cleanMessage,
                'parse_mode' => $parseMode,
            ];

            // Add reply markup if provided
            if (!empty($replyMarkupJson)) {
                $params['reply_markup'] = $replyMarkupJson;
            }

            $response = $this->telegram->sendMessage($params);

            Log::info('Telegram message sent successfully', ['response' => $response]);

            return [
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công!',
                'data' => $response,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Telegram sendMessageWithMarkup error', [
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

    /**
     * Build inline keyboard markup from button array
     */
    public function buildInlineKeyboard(array $buttons): array
    {
        $keyboard = [];

        foreach ($buttons as $row) {
            $keyboardRow = [];

            foreach ($row as $button) {
                if (empty($button['text'])) {
                    continue;
                }

                $btn = ['text' => $button['text']];

                if ($button['type'] === 'url' && !empty($button['value'])) {
                    $btn['url'] = $button['value'];
                } elseif ($button['type'] === 'callback' && !empty($button['value'])) {
                    $btn['callback_data'] = $button['value'];
                }

                $keyboardRow[] = $btn;
            }

            if (!empty($keyboardRow)) {
                $keyboard[] = $keyboardRow;
            }
        }

        return ['inline_keyboard' => $keyboard];
    }

    public function sendMessageToGroup(string $message, ?array $buttons = null): array
    {
        $chatId = config('telegram.bots.mybot.group_id');

        if (empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Chưa cấu hình TELEGRAM_GROUP_ID trong env.',
                'data' => null,
            ];
        }

        $users = $this->userRepository
            ->all(['telegram_username'])
            ->filter(function ($user) {
                return !empty($user->telegram_username);
            });


        $replyMarkupJson = !empty($buttons) ? json_encode($this->buildInlineKeyboard($buttons)) : null;

        if ($users->isNotEmpty()) {
            foreach($users as $user){
                $formattedMessage = $user->telegram_username . "\n" . $message;
                SendTelegramMessage::dispatch($chatId, $formattedMessage, $replyMarkupJson);
            }
        }
        return [
            'success' => true,
            'message' => 'Đã tạo job gửi tin vào nhóm.',
            'sent' => 1,
            'failed' => 0,
        ];
    }


    public function sendMessageToUsers(array $userIds, string $message, ?array $buttons = null): array
    {
        $dispatched = 0;

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

        $replyMarkupJson = !empty($buttons) ? json_encode($this->buildInlineKeyboard($buttons)) : null;

        foreach ($users as $user) {
            SendTelegramMessage::dispatch($user->telegram_id, $message, $replyMarkupJson);
            $dispatched++;
        }

        return [
            'success' => $dispatched > 0,
            'message' => "Đã đẩy {$dispatched} job gửi tin.",
            'sent' => $dispatched,
            'failed' => 0,
            'errors' => [],
        ];
    }


    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false): bool
    {
        try {
            $params = [
                'callback_query_id' => $callbackQueryId,
                'show_alert' => $showAlert,
            ];

            if ($text) {
                $params['text'] = $text;
            }

            $this->telegram->answerCallbackQuery($params);

            return true;
        } catch (TelegramSDKException $e) {
            Log::error('Failed to answer callback query', [
                'callback_query_id' => $callbackQueryId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Edit message reply markup to remove inline keyboard
     */
    public function editMessageReplyMarkup(string $chatId, int $messageId, ?string $replyMarkupJson = null): array
    {
        try {
            $params = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ];

            // Nếu replyMarkupJson là null hoặc rỗng, sẽ xóa keyboard
            // Nếu có giá trị, sẽ cập nhật keyboard mới
            if ($replyMarkupJson !== null) {
                $params['reply_markup'] = $replyMarkupJson;
            } else {
                // Xóa keyboard bằng cách set reply_markup là empty inline keyboard
                $params['reply_markup'] = json_encode(['inline_keyboard' => []]);
            }

            $response = $this->telegram->editMessageReplyMarkup($params);

            Log::info('Telegram message reply markup edited successfully', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);

            return [
                'success' => true,
                'message' => 'Keyboard đã được cập nhật thành công!',
                'data' => $response,
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Telegram editMessageReplyMarkup error', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
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

    public function setWebhook(string $url): array
    {
        try {
            $response = $this->telegram->setWebhook([
                'url' => $url,
                'allowed_updates' => ['message', 'callback_query', 'edited_message', 'channel_post'],
            ]);

            Log::info('Telegram webhook set', [
                'url' => $url,
                'allowed_updates' => ['message', 'callback_query', 'edited_message', 'channel_post'],
                'response' => $response,
            ]);

            return [
                'success' => true,
                'message' => 'Webhook đã được cài đặt thành công!',
            ];
        } catch (TelegramSDKException $e) {
            Log::error('Failed to set webhook', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ];
        }
    }

    public function removeWebhook(): array
    {
        try {
            $this->telegram->removeWebhook();

            return [
                'success' => true,
                'message' => 'Webhook đã được gỡ bỏ!',
            ];
        } catch (TelegramSDKException $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ];
        }
    }

    public function getWebhookInfo(): array
    {
        try {
            $info = $this->telegram->getWebhookInfo();

            return [
                'success' => true,
                'data' => $info,
            ];
        } catch (TelegramSDKException $e) {
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ];
        }
    }

    protected function parseErrorMessage(string $error): string
    {
        if (str_contains($error, 'chat not found')) {
            return 'Chat không tồn tại. Chưa có chat hoặc sai ID chat.';
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

        if (str_contains($error, 'not enough rights')) {
            return 'Bot không có quyền admin trong group.';
        }

        if (str_contains($error, 'user not found')) {
            return 'Không tìm thấy user trong group.';
        }

        return $error;
    }
}
