<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Requests\Telegram\TelegramSendRequest;
use App\Services\TelegramResponseService;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService,
        protected TelegramResponseService $responseService,
        protected UserRepositoryInterface $userRepository
    ) {}

    public function index(): View
    {
        $users = $this->userRepository
            ->all(['id', 'name', 'telegram_id', 'status'])
            ->filter(function ($user) {
                return $user->status === UserStatus::ACTIVE && !empty($user->telegram_id);
            })
            ->sortBy('name')
            ->values();

        return view('telegram.index', compact('users'));
    }

    public function responses(): View
    {
        $callbacksGrouped = $this->responseService->getCallbacksGrouped(100);
        $messagesGrouped = $this->responseService->getMessagesGrouped(100);

        // Get max IDs for real-time updates
        $latestCallbackId = $this->responseService->getLatestCallbackId($callbacksGrouped);
        $latestMessageId = $this->responseService->getLatestMessageId($messagesGrouped);

        return view('telegram.responses', compact('callbacksGrouped', 'messagesGrouped', 'latestCallbackId', 'latestMessageId'));
    }

    public function send(TelegramSendRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Filter out empty buttons
        $buttons = $this->responseService->filterEmptyButtons($validated['buttons'] ?? []);

        $result = $validated['target_type'] === 'chatgroup'
            ? $this->telegramService->sendMessageToGroup($validated['message'], $buttons)
            : $this->telegramService->sendMessageToUsers(
                $validated['user_ids'],
                $validated['message'],
                $buttons
            );

        if ($result['success']) {
            return redirect()
                ->route('telegram.index')
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('telegram.index')
            ->with('error', $result['message']);
    }
}

