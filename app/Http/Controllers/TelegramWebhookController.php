<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\TelegramCallbackRepositoryInterface;
use App\Repositories\Contracts\TelegramMessageRepositoryInterface;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService,
        protected TelegramCallbackRepositoryInterface $callbackRepository,
        protected TelegramMessageRepositoryInterface $messageRepository
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();

        Log::info('Telegram webhook received', ['data' => $data]);

        if (isset($data['callback_query'])) {
            return $this->handleCallbackQuery($data['callback_query']);
        }

        if (isset($data['message'])) {
            return $this->handleMessage($data['message']);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleCallbackQuery(array $callbackQuery): JsonResponse
    {
        $callbackId = $callbackQuery['id'];
        $callbackData = $callbackQuery['data'] ?? '';
        $from = $callbackQuery['from'] ?? [];
        $message = $callbackQuery['message'] ?? [];

        Log::info('Processing callback query', [
            'callback_id' => $callbackId,
            'callback_data' => $callbackData,
            'from' => $from,
        ]);

        $telegramUserId = $from['id'] ?? null;
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
            'chat_id' => $message['chat']['id'] ?? null,
            'raw_data' => $callbackQuery,
        ]);

        Log::info('Callback saved', ['callback' => $callback->toArray()]);

        try {
            $this->telegramService->answerCallbackQuery($callbackId, "Đã nhận: {$callbackData}");
        } catch (\Exception $e) {
            Log::error('Failed to answer callback query', [
                'callback_id' => $callbackId,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok', 'callback_id' => $callback->id]);
    }

    protected function handleMessage(array $message): JsonResponse
    {

        if (isset($message['from']['is_bot']) && $message['from']['is_bot']) {
            return response()->json(['status' => 'ok', 'skipped' => 'bot_message']);
        }

        if (!isset($message['text'])) {
            return response()->json(['status' => 'ok', 'skipped' => 'no_text']);
        }

        $from = $message['from'] ?? [];
        $chat = $message['chat'] ?? [];
        $replyTo = $message['reply_to_message'] ?? null;

        Log::info('Processing message', [
            'message_id' => $message['message_id'] ?? null,
            'from' => $from,
            'text_length' => strlen($message['text'] ?? ''),
        ]);

        $telegramUserId = $from['id'] ?? null;
        $user = $telegramUserId
            ? $this->messageRepository->findUserByTelegramId($telegramUserId)
            : null;

        $messageData = [
            'message_id' => $message['message_id'] ?? null,
            'text' => $message['text'] ?? null,
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

        Log::info('Message saved', ['message' => $savedMessage->toArray()]);

        return response()->json(['status' => 'ok', 'message_id' => $savedMessage->id]);
    }

    public function getCallbacks(Request $request): JsonResponse
    {
        $callbacks = $this->callbackRepository
            ->getWithUser($request->input('limit', 50))
            ->map(fn ($callback) => $this->formatCallback($callback));

        return response()->json([
            'success' => true,
            'data' => $callbacks,
        ]);
    }

    public function getNewCallbacks(Request $request): JsonResponse
    {
        $sinceId = $request->input('since_id', 0);

        $callbacks = $this->callbackRepository
            ->getNewSince($sinceId)
            ->map(fn ($callback) => $this->formatCallback($callback));

        return response()->json([
            'success' => true,
            'data' => $callbacks,
            'latest_id' => $callbacks->first()['id'] ?? $sinceId,
        ]);
    }
    protected function formatCallback($callback): array
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
    public function getMessages(Request $request): JsonResponse
    {
        $messages = $this->messageRepository
            ->getWithUser($request->input('limit', 50))
            ->map(fn ($message) => $this->formatMessage($message));

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function getNewMessages(Request $request): JsonResponse
    {
        $sinceId = $request->input('since_id', 0);

        $messages = $this->messageRepository
            ->getNewSince($sinceId)
            ->map(fn ($message) => $this->formatMessage($message));

        // Get the maximum ID from the messages collection
        $latestId = $sinceId;
        if ($messages->isNotEmpty()) {
            $latestId = $messages->max('id');
        }

        return response()->json([
            'success' => true,
            'data' => $messages,
            'latest_id' => $latestId,
        ]);
    }

    protected function formatMessage($message): array
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

    public function setupWebhook()
    {
        $url = route('telegram.webhook');
        $result = $this->telegramService->setWebhook($url);
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}

