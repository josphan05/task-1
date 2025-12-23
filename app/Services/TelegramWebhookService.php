<?php

namespace App\Services;

use App\Models\TelegramConversation;
use App\Repositories\Contracts\TelegramCallbackRepositoryInterface;
use App\Repositories\Contracts\TelegramMessageRepositoryInterface;
use Illuminate\Support\Facades\Log;

class TelegramWebhookService
{
    public function __construct(
        protected TelegramService $telegramService,
        protected TelegramCallbackRepositoryInterface $callbackRepository,
        protected TelegramMessageRepositoryInterface $messageRepository,
        protected TelegramConversationService $conversationService,
        protected QuestionSetCommandService $commandService
    ) {}

    /**
     * Handle callback query from Telegram
     *
     * @param array $callbackQuery
     * @return array
     */
    public function handleCallbackQuery(array $callbackQuery): array
    {
        $callbackId = $callbackQuery['id'];
        $callbackData = $callbackQuery['data'] ?? '';
        $from = $callbackQuery['from'] ?? [];
        $message = $callbackQuery['message'] ?? [];

        $telegramUserId = $from['id'] ?? null;
        $chatId = $message['chat']['id'] ?? null;

        // Xử lý conversation callback trước khi lưu vào DB
        $isConversationCallback = false;
        if ($telegramUserId && $chatId) {
            try {
                $messageId = $message['message_id'] ?? null;
                $isConversationCallback = $this->conversationService->handleCallback($telegramUserId, $chatId, $callbackData, $messageId);
            } catch (\Exception $e) {
                Log::error('Error handling conversation callback', [
                    'telegram_user_id' => $telegramUserId,
                    'callback_data' => $callbackData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $user = $telegramUserId
            ? $this->callbackRepository->findUserByTelegramId($telegramUserId)
            : null;

        $callback = $this->callbackRepository->create([
            'callback_id' => $callbackId,
            'callback_data' => $callbackData,
            'message_text' => $message['text'] ?? null,
            'telegram_user_id' => $telegramUserId,
            'telegram_username' => $from['username'] ?? null,
            'telegram_first_name' => $from['first_name'] ?? null,
            'telegram_last_name' => $from['last_name'] ?? null,
            'user_id' => $user?->id,
            'message_id' => $message['message_id'] ?? null,
            'chat_id' => $chatId,
            'raw_data' => $callbackQuery,
        ]);

        try {
            $this->telegramService->answerCallbackQuery($callbackId, "Đã nhận: {$callbackData}");
        } catch (\Exception $e) {
            Log::error('Failed to answer callback query', [
                'callback_id' => $callbackId,
                'error' => $e->getMessage(),
            ]);
        }

        // Nếu callback không phải từ conversation và có message_id, xóa keyboard
        if (!$isConversationCallback && $chatId && isset($message['message_id'])) {
            try {
                $this->telegramService->editMessageReplyMarkup($chatId, $message['message_id']);
            } catch (\Exception $e) {
                Log::error('Failed to remove keyboard for non-conversation callback', [
                    'chat_id' => $chatId,
                    'message_id' => $message['message_id'],
                    'callback_data' => $callbackData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['status' => 'ok', 'callback_id' => $callback->id];
    }

    /**
     * Handle message from Telegram
     *
     * @param array $message
     * @return array
     */
    public function handleMessage(array $message): array
    {
        if (isset($message['from']['is_bot']) && $message['from']['is_bot']) {
            return ['status' => 'ok', 'skipped' => 'bot_message'];
        }

        if (!isset($message['text'])) {
            return ['status' => 'ok', 'skipped' => 'no_text'];
        }

        $from = $message['from'] ?? [];
        $chat = $message['chat'] ?? [];
        $replyTo = $message['reply_to_message'] ?? null;

        $telegramUserId = $from['id'] ?? null;
        $chatId = $chat['id'] ?? null;
        $messageText = $message['text'] ?? '';

        if (str_starts_with($messageText, '/')) {
            $this->handleCommand($telegramUserId, $chatId, $messageText);
        } else {
            if ($telegramUserId && $chatId) {
                $conversation = TelegramConversation::where('telegram_user_id', $telegramUserId)->first();
                if ($conversation && $conversation->step !== null && $conversation->step !== 'completed') {
                    $this->conversationService->handleConversation($telegramUserId, $chatId, $messageText);
                }
            }
        }

        $user = $telegramUserId
            ? $this->messageRepository->findUserByTelegramId($telegramUserId)
            : null;

        $messageData = [
            'message_id' => $message['message_id'] ?? null,
            'text' => $messageText,
            'telegram_user_id' => $telegramUserId,
            'telegram_username' => $from['username'] ?? null,
            'telegram_first_name' => $from['first_name'] ?? null,
            'telegram_last_name' => $from['last_name'] ?? null,
            'user_id' => $user?->id,
            'chat_id' => $chat['id'] ?? null,
            'reply_to_message_id' => $replyTo['message_id'] ?? null,
            'raw_data' => $message,
        ];

        $savedMessage = $this->messageRepository->updateOrCreate(
            ['message_id' => $messageData['message_id']],
            $messageData
        );

        return ['status' => 'ok', 'message_id' => $savedMessage->id];
    }

    /**
     * Handle command from Telegram
     *
     * @param int $telegramUserId
     * @param string $chatId
     * @param string $command
     * @return void
     */
    protected function handleCommand(int $telegramUserId, string $chatId, string $command): void
    {
        $commandMapping = $this->commandService->findByCommand($command);

        if ($commandMapping && $commandMapping->questionSet) {
            $conversation = TelegramConversation::firstOrCreate(
                ['telegram_user_id' => $telegramUserId],
                ['step' => null, 'data' => [], 'current_question_order' => null]
            );

            $conversation->question_set_id = $commandMapping->question_set_id;
            $conversation->step = null;
            $conversation->current_question_order = null;
            $conversation->data = [];
            $conversation->save();

            if ($commandMapping->response_message) {
                $this->telegramService->sendMessageWithMarkup($chatId, $commandMapping->response_message);
            }

            $this->conversationService->startConversationWithQuestionSet($telegramUserId, $chatId, $commandMapping->questionSet);
        } else {
            $this->conversationService->handleConversation($telegramUserId, $chatId, $command);
        }
    }

    /**
     * Format callback for API response
     *
     * @param mixed $callback
     * @return array
     */
    public function formatCallback($callback): array
    {
        return [
            'id' => $callback->id,
            'callback_data' => $callback->callback_data,
            'message_text' => $callback->message_text,
            'message_id' => $callback->message_id,
            'chat_id' => $callback->chat_id,
            'display_name' => $callback->display_name,
            'telegram_full_name' => $callback->telegram_full_name,
            'user_name' => $callback->user?->name,
            'created_at' => $callback->created_at->format('d/m/Y H:i:s'),
            'time_ago' => $callback->created_at->diffForHumans(),
        ];
    }

    /**
     * Format message for API response
     *
     * @param mixed $message
     * @return array
     */
    public function formatMessage($message): array
    {
        return [
            'id' => $message->id,
            'text' => $message->text,
            'message_id' => $message->message_id,
            'chat_id' => $message->chat_id,
            'reply_to_message_id' => $message->reply_to_message_id,
            'display_name' => $message->display_name,
            'telegram_full_name' => $message->telegram_full_name,
            'user_name' => $message->user?->name,
            'created_at' => $message->created_at->format('d/m/Y H:i:s'),
            'time_ago' => $message->created_at->diffForHumans(),
        ];
    }
}

