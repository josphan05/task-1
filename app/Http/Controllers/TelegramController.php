<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\TelegramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                return $user->status === 'active' && !empty($user->telegram_id);
            })
            ->sortBy('name')
            ->values();

        return view('telegram.index', compact('users'));
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
            'message' => ['required', 'string', 'min:1', 'max:4096'],
        ], [
            'user_ids.required' => 'Vui lòng chọn ít nhất một người dùng.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một người dùng.',
            'message.required' => 'Vui lòng nhập nội dung tin nhắn.',
            'message.max' => 'Tin nhắn không được vượt quá 4096 ký tự.',
        ]);

        $result = $this->telegramService->sendMessageToUsers(
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

