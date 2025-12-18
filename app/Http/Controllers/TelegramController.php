<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Repositories\Contracts\TelegramCallbackRepositoryInterface;
use App\Repositories\Contracts\TelegramMessageRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Requests\Telegram\TelegramSendRequest;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService,
        protected UserRepositoryInterface $userRepository,
        protected TelegramCallbackRepositoryInterface $callbackRepository,
        protected TelegramMessageRepositoryInterface $messageRepository
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
        $callbacksGrouped = $this->callbackRepository->getGroupedByMessageId(100);
        $messagesGrouped = $this->messageRepository->getGroupedByReplyTo(100);

        // Get max IDs for real-time updates
        $latestCallbackId = $callbacksGrouped->flatten()->max('id') ?? 0;
        $latestMessageId = $messagesGrouped->flatten()->max('id') ?? 0;

        return view('telegram.responses', compact('callbacksGrouped', 'messagesGrouped', 'latestCallbackId', 'latestMessageId'));
    }

    public function send(TelegramSendRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Filter out empty buttons
        $buttons = $this->filterEmptyButtons($validated['buttons'] ?? []);

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

    /**
     * Filter out empty button rows and buttons
     */
    protected function filterEmptyButtons(?array $buttons): ?array
    {
        if (empty($buttons)) {
            return null;
        }

        $filtered = [];

        foreach ($buttons as $row) {
            $filteredRow = [];

            foreach ($row as $button) {
                if (!empty($button['text']) && !empty($button['value'])) {
                    $filteredRow[] = $button;
                }
            }

            if (!empty($filteredRow)) {
                $filtered[] = $filteredRow;
            }
        }

        return !empty($filtered) ? $filtered : null;
    }

}

