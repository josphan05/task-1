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
        $data = $request->all();

        Log::info('Telegram webhook received', ['data' => $data]);

        if (isset($data['callback_query'])) {
            $result = $this->webhookService->handleCallbackQuery($data['callback_query']);
            return response()->json($result);
        }

        if (isset($data['message'])) {
            $result = $this->webhookService->handleMessage($data['message']);
            return response()->json($result);
        }

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

