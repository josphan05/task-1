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

        // Kiểm tra xem đang sửa hay điền mới
        // Nếu field này đã có trong data và có nhiều hơn 1 field, nghĩa là đang sửa
        $wasEditing = isset($data[$currentQuestion->field_name]) && count($data) > 1;

        $data[$currentQuestion->field_name] = $answer;

        if ($wasEditing) {
            // Đang sửa, cập nhật data và quay lại summary
            $conversation->updateStep('completed', null, $data);

            // Gửi thông báo xác nhận đã sửa
            $questionText = $currentQuestion->question_text;
            $confirmMessage = "✅ <b>Đã cập nhật!</b>\n\n" .
                             "Câu hỏi: <b>" . htmlspecialchars($questionText) . "</b>\n" .
                             "Câu trả lời mới: <code>" . htmlspecialchars($answer) . "</code>";
            $this->telegramService->sendMessageWithMarkup($chatId, $confirmMessage, 'HTML');

            $data = $conversation->data ?? [];
            $summaryMessageId = $data['_summary_message_id'] ?? null;
            $this->completeConversation($conversation, $chatId, $questionSet, $summaryMessageId);
        } else {
            // Đang điền form mới, tiếp tục như bình thường
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
    }

    protected function askQuestion(TelegramConversation $conversation, string $chatId, Question $question, ?string $currentAnswer = null): void
    {
        $message = $question->question_text;

        // Nếu đang sửa và có câu trả lời hiện tại, hiển thị nó
        if ($currentAnswer !== null && $currentAnswer !== '') {
            $message = "✏️ <b>Sửa câu hỏi:</b>\n\n";
            $message .= "<b>" . htmlspecialchars($question->question_text) . "</b>\n\n";
            $message .= "📝 <b>Câu trả lời hiện tại:</b> <code>" . htmlspecialchars($currentAnswer) . "</code>\n\n";
            if($question->options != null){
                $message .= "Vui lòng chọn lại từ các tùy chọn bên dưới:";
            }else{
                $message .= "Vui lòng nhập câu trả lời mới:";
            }
        }

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

    protected function completeConversation(TelegramConversation $conversation, string $chatId, QuestionSet $questionSet, ?int $existingMessageId = null): void
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
            ]
        ];

        $buttons = $this->filterButtons($buttons, ['review_info']);

        $keyboardJson = json_encode($this->telegramService->buildInlineKeyboard($buttons));

        if ($existingMessageId) {
            $result = $this->telegramService->editMessageText($chatId, $existingMessageId, $message, 'HTML', $keyboardJson);
            if ($result['success'] && isset($result['data'])) {
                $data['_summary_message_id'] = $existingMessageId;
                $conversation->updateStep('completed', null, $data);
            }
        } else {
            $result = $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML', $keyboardJson);
            if ($result['success'] && isset($result['data'])) {
                $response = $result['data'];
                $messageId = is_object($response) && method_exists($response, 'getMessageId')
                    ? $response->getMessageId()
                    : ($response['message_id'] ?? null);

                if ($messageId) {
                    $data['_summary_message_id'] = $messageId;
                    $conversation->updateStep('completed', null, $data);
                }
            }
        }
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

    public function handleCallback(int $telegramUserId, string $chatId, string $callbackData, ?int $messageId = null): bool
    {
        $conversation = TelegramConversation::where('telegram_user_id', $telegramUserId)->first();

        if (str_starts_with($callbackData, 'answer_')) {
            if (!$conversation) {
                return false;
            }
            $this->handleQuestionAnswer($conversation, $chatId, $callbackData, $messageId);
            return true;
        }

        if (str_starts_with($callbackData, 'edit_question_')) {
            if (!$conversation) {
                return false;
            }
            $this->handleEditQuestion($conversation, $chatId, $callbackData, $messageId);
            return true;
        }

        $conversationCallbacks = ['confirm_send', 'edit_form', 'review_info'];
        if (!in_array($callbackData, $conversationCallbacks)) {
            return false;
        }

        if (!$conversation) {
            return false;
        }

        switch ($callbackData) {
            case 'confirm_send':
                if ($conversation->step !== 'completed') {
                    $this->telegramService->sendMessageWithMarkup($chatId, "Vui lòng hoàn thành form trước.");
                    return false;
                }
                $this->handleConfirmSend($conversation, $chatId, $messageId);
                return true;
            case 'edit_form':
                if ($conversation->step !== 'completed' && !$this->isEditingMode($conversation)) {
                    $this->telegramService->sendMessageWithMarkup($chatId, "Vui lòng hoàn thành form trước.");
                    return false;
                }
                $this->handleEditForm($conversation, $chatId, $messageId);
                return true;
            case 'review_info':
                if ($conversation->step !== 'completed' && !$this->isEditingMode($conversation)) {
                    $this->telegramService->sendMessageWithMarkup($chatId, "Vui lòng hoàn thành form trước.");
                    return false;
                }
                $this->handleReviewInfo($conversation, $chatId, $messageId);
                return true;
            default:
                return false;
        }
    }

    protected function handleQuestionAnswer(TelegramConversation $conversation, string $chatId, string $callbackData, ?int $messageId = null): void
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

        if ($messageId) {
            $this->telegramService->editMessageReplyMarkup($chatId, $messageId);
        }

        $data = $conversation->data ?? [];

        $wasEditing = isset($data[$fieldName]) && count($data) > 1;

        $data[$fieldName] = $answerValue;

        if ($wasEditing) {
            $conversation->updateStep('completed', null, $data);

            $questionText = $currentQuestion->question_text;
            $confirmMessage = "✅ <b>Đã cập nhật!</b>\n\n" .
                             "Câu hỏi: <b>" . htmlspecialchars($questionText) . "</b>\n" .
                             "Câu trả lời mới: <code>" . htmlspecialchars($answerValue) . "</code>";
            $this->telegramService->sendMessageWithMarkup($chatId, $confirmMessage, 'HTML');

            $data = $conversation->data ?? [];
            $summaryMessageId = $data['_summary_message_id'] ?? null;
            $this->completeConversation($conversation, $chatId, $questionSet, $summaryMessageId);
        } else {
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
    }

    protected function handleConfirmSend(TelegramConversation $conversation, string $chatId, ?int $messageId = null): void
    {
        $data = $conversation->data ?? [];

        Log::info('Feedback submitted', [
            'telegram_user_id' => $conversation->telegram_user_id,
            'question_set_id' => $conversation->question_set_id,
            'data' => $data
        ]);

        if ($messageId) {
            $this->telegramService->editMessageReplyMarkup($chatId, $messageId);
        }

        $message = "✅ <b>Đã gửi thành công!</b>\n\n" .
                   "Cảm ơn bạn đã phản ánh. Chúng tôi sẽ xử lý sớm nhất có thể.";
        $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML');

        $conversation->reset();
    }

    protected function handleEditForm(TelegramConversation $conversation, string $chatId, ?int $messageId = null): void
    {
        $questionSet = $conversation->questionSet;
        if (!$questionSet) {
            $this->telegramService->sendMessageWithMarkup($chatId, "Lỗi: Không tìm thấy bộ câu hỏi.");
            return;
        }

        if ($messageId) {
            $this->telegramService->editMessageReplyMarkup($chatId, $messageId);
        }

        $data = $conversation->data ?? [];
        $questions = $questionSet->questions()->orderBy('order')->get();

        $message = "✏️ <b>Chọn câu hỏi bạn muốn sửa:</b>\n\n";
        $buttons = [];
        $row = [];

        foreach ($questions as $question) {
            $answer = $data[$question->field_name] ?? null;
            if ($answer) {
                $questionText = mb_substr($question->question_text, 0, 30);
                if (mb_strlen($question->question_text) > 30) {
                    $questionText .= '...';
                }
                $row[] = [
                    'text' => $questionText,
                    'type' => 'callback',
                    'value' => 'edit_question_' . $question->field_name,
                ];
                if (count($row) >= 2) {
                    $buttons[] = $row;
                    $row = [];
                }
            }
        }

        if (!empty($row)) {
            $buttons[] = $row;
        }

        if (!empty($buttons)) {
            $buttons[] = [
                [
                    'text' => '🔙 Quay lại',
                    'type' => 'callback',
                    'value' => 'review_info'
                ]
            ];
        } else {
            $message = "Không có câu hỏi nào để sửa.";
            $buttons = [
                [
                    [
                        'text' => '🔙 Quay lại',
                        'type' => 'callback',
                        'value' => 'review_info'
                    ]
                ]
            ];
        }

        $keyboardJson = json_encode($this->telegramService->buildInlineKeyboard($buttons));

        $editFormMessageId = $data['_edit_form_message_id'] ?? null;

        if ($editFormMessageId) {
            $editResult = $this->telegramService->editMessageText($chatId, $editFormMessageId, $message, 'HTML', $keyboardJson);

            if (!$editResult['success']) {
                $editFormMessageId = null;
                unset($data['_edit_form_message_id']);
            }
        }

        if (!$editFormMessageId) {
            $result = $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML', $keyboardJson);
            if ($result['success'] && isset($result['data'])) {
                $response = $result['data'];
                $newMessageId = is_object($response) && method_exists($response, 'getMessageId')
                    ? $response->getMessageId()
                    : ($response['message_id'] ?? null);

                if ($newMessageId) {
                    $data['_edit_form_message_id'] = $newMessageId;
                    $conversation->updateStep($conversation->step, $conversation->current_question_order, $data);
                }
            }
        }
    }

    protected function handleEditQuestion(TelegramConversation $conversation, string $chatId, string $callbackData, ?int $messageId = null): void
    {
        $questionSet = $conversation->questionSet;
        if (!$questionSet) {
            return;
        }

        if ($messageId) {
            $this->telegramService->editMessageReplyMarkup($chatId, $messageId);
        }

        $parts = explode('_', $callbackData, 3);
        if (count($parts) < 3 || $parts[0] !== 'edit' || $parts[1] !== 'question') {
            return;
        }

        $fieldName = $parts[2] ?? '';
        $question = $questionSet->questions()
            ->where('field_name', $fieldName)
            ->first();

        if (!$question) {
            return;
        }

        $data = $conversation->data ?? [];
        $currentAnswer = $data[$fieldName] ?? '';

        $editFormMessageId = $data['_edit_form_message_id'] ?? null;

        $message = "✏️ <b>Sửa câu hỏi:</b>\n\n";
        $message .= "<b>" . htmlspecialchars($question->question_text) . "</b>\n\n";
        if ($currentAnswer) {
            $message .= "📝 <b>Câu trả lời hiện tại:</b> <code>" . htmlspecialchars($currentAnswer) . "</code>\n\n";
        }
        if($question->options != null){
            $message .= "Vui lòng chọn lại từ các tùy chọn bên dưới:";
        }else{
            $message .= "Vui lòng nhập câu trả lời mới:";
        }

        $conversation->updateStep($fieldName, $question->order);

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

        if ($editFormMessageId) {
            $editResult = $this->telegramService->editMessageText($chatId, $editFormMessageId, $message, 'HTML', $keyboardJson);
            if (!$editResult['success']) {
                $result = $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML', $keyboardJson);
                if ($result['success'] && isset($result['data'])) {
                    $response = $result['data'];
                    $newMessageId = is_object($response) && method_exists($response, 'getMessageId')
                        ? $response->getMessageId()
                        : ($response['message_id'] ?? null);
                    if ($newMessageId) {
                        $data['_edit_form_message_id'] = $newMessageId;
                        $conversation->updateStep($fieldName, $question->order, $data);
                    }
                }
            }
        } else {
            $result = $this->telegramService->sendMessageWithMarkup($chatId, $message, 'HTML', $keyboardJson);
            if ($result['success'] && isset($result['data'])) {
                $response = $result['data'];
                $newMessageId = is_object($response) && method_exists($response, 'getMessageId')
                    ? $response->getMessageId()
                    : ($response['message_id'] ?? null);
                if ($newMessageId) {
                    $data['_edit_form_message_id'] = $newMessageId;
                    $conversation->updateStep($fieldName, $question->order, $data);
                }
            }
        }
    }

    protected function handleReviewInfo(TelegramConversation $conversation, string $chatId, ?int $messageId = null): void
    {
        $questionSet = $conversation->questionSet;
        if (!$questionSet) {
            $this->telegramService->sendMessageWithMarkup($chatId, "Lỗi: Không tìm thấy bộ câu hỏi.");
            return;
        }

        if ($messageId) {
            $this->telegramService->editMessageReplyMarkup($chatId, $messageId);
        }

        $conversation->updateStep('completed', null);

        $data = $conversation->data ?? [];

        $editFormMessageId = $data['_edit_form_message_id'] ?? null;
        if ($editFormMessageId) {
            $this->telegramService->deleteMessage($chatId, $editFormMessageId);
            unset($data['_edit_form_message_id']);
            $conversation->updateStep('completed', null, $data);
        }

        $summaryMessageId = $data['_summary_message_id'] ?? null;

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

        $buttons = $this->filterButtons($buttons, ['review_info']);

        $keyboardJson = json_encode($this->telegramService->buildInlineKeyboard($buttons));

        if ($summaryMessageId) {
            $this->telegramService->editMessageText($chatId, $summaryMessageId, $summary, 'HTML', $keyboardJson);
            $conversation->updateStep('completed', null, $data);
        } else {
            $result = $this->telegramService->sendMessageWithMarkup($chatId, $summary, 'HTML', $keyboardJson);
            if ($result['success'] && isset($result['data'])) {
                $response = $result['data'];
                $newMessageId = is_object($response) && method_exists($response, 'getMessageId')
                    ? $response->getMessageId()
                    : ($response['message_id'] ?? null);

                if ($newMessageId) {
                    $data['_summary_message_id'] = $newMessageId;
                    $conversation->updateStep('completed', null, $data);
                }
            }
        }
    }

    /**
     */
    protected function isEditingMode(TelegramConversation $conversation): bool
    {
        $data = $conversation->data ?? [];
        return !empty($data) &&
               $conversation->step !== null &&
               $conversation->step !== 'completed' &&
               count($data) > 1;
    }

    protected function filterButtons(array $buttons, array $excludeValues): array
    {
        $filtered = [];
        foreach ($buttons as $row) {
            $filteredRow = [];
            foreach ($row as $button) {
                $value = $button['value'] ?? null;
                if ($value && !in_array($value, $excludeValues)) {
                    $filteredRow[] = $button;
                }
            }
            if (!empty($filteredRow)) {
                $filtered[] = $filteredRow;
            }
        }
        return $filtered;
    }
}
