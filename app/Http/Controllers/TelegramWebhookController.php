<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\TelegramCallbackRepositoryInterface;
use App\Repositories\Contracts\TelegramMessageRepositoryInterface;
use App\Services\TelegramService;
use App\Services\TelegramWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService,
        protected TelegramWebhookService $webhookService,
        protected TelegramCallbackRepositoryInterface $callbackRepository,
        protected TelegramMessageRepositoryInterface $messageRepository
    ) {}

    public function handle(Request $request): JsonResponse
    {
        // Log ngay từ đầu để đảm bảo request đã đến
        $rawContent = $request->getContent();

        Log::info('=== WEBHOOK REQUEST RECEIVED ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_length' => strlen($rawContent),
            'content_preview' => substr($rawContent, 0, 500), // Log 500 ký tự đầu để xem có gì
        ]);

        $data = $request->all();

        Log::info('Telegram webhook received', [
            'has_callback_query' => isset($data['callback_query']),
            'has_message' => isset($data['message']),
            'has_edited_message' => isset($data['edited_message']),
            'has_channel_post' => isset($data['channel_post']),
            'update_id' => $data['update_id'] ?? null,
            'keys' => array_keys($data),
            'raw_data' => $data, // Log toàn bộ data để debug
        ]);

        if (isset($data['callback_query'])) {
            Log::info('Callback query detected', [
                'callback_id' => $data['callback_query']['id'] ?? null,
                'callback_data' => $data['callback_query']['data'] ?? null,
            ]);
            $result = $this->webhookService->handleCallbackQuery($data['callback_query']);
            return response()->json($result);
        }

        if (isset($data['message'])) {
            $result = $this->webhookService->handleMessage($data['message']);
            return response()->json($result);
        }

        Log::warning('Webhook received but no callback_query or message', ['data' => $data]);
        return response()->json(['status' => 'ok']);
    }

    public function getCallbacks(Request $request): JsonResponse
    {
        $callbacks = $this->callbackRepository
            ->getWithUser($request->input('limit', 50))
            ->map(fn ($callback) => $this->webhookService->formatCallback($callback));

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
            ->map(fn ($callback) => $this->webhookService->formatCallback($callback));

        return response()->json([
            'success' => true,
            'data' => $callbacks,
            'latest_id' => $callbacks->first()['id'] ?? $sinceId,
        ]);
    }

    public function getMessages(Request $request): JsonResponse
    {
        $messages = $this->messageRepository
            ->getWithUser($request->input('limit', 50))
            ->map(fn ($message) => $this->webhookService->formatMessage($message));

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
            ->map(fn ($message) => $this->webhookService->formatMessage($message));

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

    public function setupWebhook(): JsonResponse
    {
        $url = route('telegram.webhook');
        $result = $this->telegramService->setWebhook($url);
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}

