<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Http\Requests\Telegram\TelegramSendRequest;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService,
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

    public function send(TelegramSendRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $validated['target_type'] === 'chatgroup'
            ? $this->telegramService->sendMessageToGroup($validated['message'])
            : $this->telegramService->sendMessageToUsers(
                $validated['user_ids'],
                $validated['message']
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

