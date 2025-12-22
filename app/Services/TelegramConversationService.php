<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\TelegramConversation;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class TelegramConversationService
{
    public function __construct(
        protected TelegramService $telegramService
    ) {}

    public function handleConversation(int $telegramUserId, string $chatId, string $messageText): void
    {
        $conversation = TelegramConversation::firstOrCreate(
            ['telegram_user_id' => $telegramUserId],
            ['step' => null, 'data' => [], 'current_question_order' => null]
        );

        // Nếu chưa có question_set, load question set mặc định
        if (!$conversation->question_set_id) {
            $questionSet = QuestionSet::getDefault();
            if (!$questionSet) {
                Log::warning('No default question set found', ['telegram_user_id' => $telegramUserId]);
                $this->telegramService->sendMessageWithMarkup($chatId, "Xin lỗi, hệ thống đang bảo trì.");
                return;
            }
            $conversation->question_set_id = $questionSet->id;
            $conversation->save();
        }

        $questionSet = $conversation->questionSet;
        if (!$questionSet || !$questionSet->is_active) {
            $this->telegramService->sendMessageWithMarkup($chatId, "Xin lỗi, bộ câu hỏi không còn hoạt động.");
            return;
        }

        if (!$conversation->step) {
            $this->startConversation($conversation, $chatId, $questionSet);
            return;
        }

        $this->handleAnswer($conversation, $chatId, $questionSet, $messageText);
    }

    protected function startConversation(TelegramConversation $conversation, string $chatId, QuestionSet $questionSet): void
    {
        $startMessage = $questionSet->start_message
            ?: "Xin chào! Tôi là bot hỗ trợ. Hãy trả lời các câu hỏi sau:";

        $this->telegramService->sendMessageWithMarkup($chatId, $startMessage);

        $firstQuestion = $questionSet->questions()->orderBy('order')->first();

        if ($firstQuestion) {
            $this->askQuestion($conversation, $chatId, $firstQuestion);
        }
    }

    public function startConversationWithQuestionSet(int $telegramUserId, string $chatId, QuestionSet $questionSet): void
    {
        $conversation = TelegramConversation::firstOrCreate(
            ['telegram_user_id' => $telegramUserId],
            ['step' => null, 'data' => [], 'current_question_order' => null]
        );

        $conversation->question_set_id = $questionSet->id;
        $conversation->save();

        $this->startConversation($conversation, $chatId, $questionSet);
    }

    protected function handleAnswer(TelegramConversation $conversation, string $chatId, QuestionSet $questionSet, string $answer): void
    {
        $currentQuestion = $questionSet->questions()
            ->where('field_name', $conversation->step)
            ->where('order', $conversation->current_question_order)
            ->first();

        if (!$currentQuestion) {
            Log::warning('Current question not found', [
                'step' => $conversation->step,
                'order' => $conversation->current_question_order
            ]);
            $this->startConversation($conversation, $chatId, $questionSet);
            return;
        }

        if (!empty($currentQuestion->options) && is_array($currentQuestion->options)) {
            $message = "Vui lòng chọn một trong các tùy chọn bằng cách nhấn vào nút bên dưới.";
            $this->telegramService->sendMessageWithMarkup($chatId, $message);
            $this->askQuestion($conversation, $chatId, $currentQuestion);
            return;
        }

        $validation = $currentQuestion->validateAnswer($answer);

        if (!$validation['valid']) {
            $errorMessage = !empty($validation['errors'])
                ? implode("\n", $validation['errors'])
                : "Câu trả lời không hợp lệ. Vui lòng thử lại.";
            $this->telegramService->sendMessageWithMarkup($chatId, $errorMessage);
            return;
        }
        $data = $conversation->data ?? [];
        $data[$currentQuestion->field_name] = $answer;
        $conversation->updateStep(null, null, $data);
        $nextQuestion = $questionSet->questions()
            ->where('order', '>', $currentQuestion->order)
            ->orderBy('order')
            ->first();

        if ($nextQuestion) {
            $this->askQuestion($conversation, $chatId, $nextQuestion);
        } else {
            $this->completeConversation($conversation, $chatId, $questionSet);
        }
    }

    protected function askQuestion(TelegramConversation $conversation, string $chatId, Question $question): void
    {
        $message = $question->question_text;
        $keyboardJson = null;
        if (!empty($question->options) && is_array($question->options)) {
            $buttons = [];
            $row = [];

            foreach ($question->options as $option) {
                $row[] = [
                    'text' => $option['text'] ?? $option['value'] ?? '',
                    'type' => 'callback',
                    'value' => 'answer_' . $question->field_name . '_' . ($option['value'] ?? $option['text'] ?? ''),
                ];
                if (count($row) >= 2) {
                    $buttons[] = $row;
                    $row = [];
                }
            }
            if (!empty($row)) {
                $buttons[] = $row;
            }

            if (!empty($buttons)) {
                $keyboardJson = json_encode($this->telegramService->buildInlineKeyboard($buttons));
            }
        }

        $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML', $keyboardJson);
        $conversation->updateStep($question->field_name, $question->order);
    }

    protected function completeConversation(TelegramConversation $conversation, string $chatId, QuestionSet $questionSet): void
    {
        $data = $conversation->data ?? [];
        $conversation->updateStep('completed', null);

        $summary = $this->buildSummaryMessage($questionSet, $data);

        $completionMessage = $questionSet->completion_message
            ?: "Cảm ơn, thông tin đã được ghi nhận.";

        $message = $completionMessage . "\n\n" . $summary;

        $buttons = $questionSet->completion_buttons ?? [
            [
                [
                    'text' => '✅ Xác nhận và gửi',
                    'type' => 'callback',
                    'value' => 'confirm_send'
                ],
                [
                    'text' => '✏️ Sửa lại',
                    'type' => 'callback',
                    'value' => 'edit_form'
                ]
            ],
            [
                [
                    'text' => '📋 Xem lại thông tin',
                    'type' => 'callback',
                    'value' => 'review_info'
                ]
            ]
        ];

        $keyboardJson = json_encode($this->telegramService->buildInlineKeyboard($buttons));
        $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML', $keyboardJson);
    }

    protected function buildSummaryMessage(QuestionSet $questionSet, array $data): string
    {
        $questions = $questionSet->questions()->orderBy('order')->get();
        $summary = "📋 <b>Thông tin của bạn:</b>\n\n";

        foreach ($questions as $question) {
            $answer = $data[$question->field_name] ?? 'N/A';
            $summary .= "• <b>" . htmlspecialchars($question->question_text) . "</b>\n";
            $summary .= "  " . htmlspecialchars($answer) . "\n\n";
        }

        return trim($summary);
    }

    public function handleCallback(int $telegramUserId, string $chatId, string $callbackData): void
    {
        $conversation = TelegramConversation::where('telegram_user_id', $telegramUserId)->first();

        // Xử lý callback từ question answer (không cần conversation)
        if (str_starts_with($callbackData, 'answer_')) {
            if (!$conversation) {
                Log::warning('Answer callback without conversation', [
                    'telegram_user_id' => $telegramUserId,
                    'callback_data' => $callbackData
                ]);
                return;
            }
            $this->handleQuestionAnswer($conversation, $chatId, $callbackData);
            return;
        }

        // Các callback khác cần conversation
        if (!$conversation) {
            Log::info('Callback without conversation, ignoring', [
                'telegram_user_id' => $telegramUserId,
                'callback_data' => $callbackData
            ]);
            return;
        }

        if ($conversation->step !== 'completed') {
            $message = "Vui lòng hoàn thành form trước.";
            $this->telegramService->sendMessageWithMarkup($chatId, $message);
            return;
        }

        switch ($callbackData) {
            case 'confirm_send':
                $this->handleConfirmSend($conversation, $chatId);
                break;
            case 'edit_form':
                $this->handleEditForm($conversation, $chatId);
                break;
            case 'review_info':
                $this->handleReviewInfo($conversation, $chatId);
                break;
            default:
                Log::info('Unknown callback data', [
                    'telegram_user_id' => $telegramUserId,
                    'callback_data' => $callbackData
                ]);
                break;
        }
    }

    protected function handleQuestionAnswer(TelegramConversation $conversation, string $chatId, string $callbackData): void
    {
        $parts = explode('_', $callbackData, 3);
        if (count($parts) < 3 || $parts[0] !== 'answer') {
            return;
        }

        $fieldName = $parts[1];
        $answerValue = $parts[2] ?? '';

        $questionSet = $conversation->questionSet;
        if (!$questionSet) {
            return;
        }

        $currentQuestion = $questionSet->questions()
            ->where('field_name', $fieldName)
            ->where('order', $conversation->current_question_order)
            ->first();

        if (!$currentQuestion) {
            return;
        }

        $data = $conversation->data ?? [];
        $data[$fieldName] = $answerValue;
        $conversation->updateStep(null, null, $data);

        $nextQuestion = $questionSet->questions()
            ->where('order', '>', $currentQuestion->order)
            ->orderBy('order')
            ->first();

        if ($nextQuestion) {
            $this->askQuestion($conversation, $chatId, $nextQuestion);
        } else {
            $this->completeConversation($conversation, $chatId, $questionSet);
        }
    }

    protected function handleConfirmSend(TelegramConversation $conversation, string $chatId): void
    {
        $data = $conversation->data ?? [];

        Log::info('Feedback submitted', [
            'telegram_user_id' => $conversation->telegram_user_id,
            'question_set_id' => $conversation->question_set_id,
            'data' => $data
        ]);

        $message = "✅ <b>Đã gửi thành công!</b>\n\n" .
                   "Cảm ơn bạn đã phản ánh. Chúng tôi sẽ xử lý sớm nhất có thể.";
        $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML');

        $conversation->reset();
    }

    protected function handleEditForm(TelegramConversation $conversation, string $chatId): void
    {
        $questionSet = $conversation->questionSet;
        if (!$questionSet) {
            $this->telegramService->sendMessageWithMarkup($chatId, "Lỗi: Không tìm thấy bộ câu hỏi.");
            return;
        }

        $conversation->data = [];
        $conversation->save();
        $this->startConversation($conversation, $chatId, $questionSet);
    }

    protected function handleReviewInfo(TelegramConversation $conversation, string $chatId): void
    {
        $questionSet = $conversation->questionSet;
        if (!$questionSet) {
            $this->telegramService->sendMessageWithMarkup($chatId, "Lỗi: Không tìm thấy bộ câu hỏi.");
            return;
        }

        $data = $conversation->data ?? [];
        $summary = $this->buildSummaryMessage($questionSet, $data);

        $buttons = $questionSet->completion_buttons ?? [
            [
                [
                    'text' => '✅ Xác nhận và gửi',
                    'type' => 'callback',
                    'value' => 'confirm_send'
                ],
                [
                    'text' => '✏️ Sửa lại',
                    'type' => 'callback',
                    'value' => 'edit_form'
                ]
            ]
        ];

        $keyboardJson = json_encode($this->telegramService->buildInlineKeyboard($buttons));
        $this->telegramService->sendMessageWithMarkup($chatId, $summary, 'HTML', $keyboardJson);
    }
}
