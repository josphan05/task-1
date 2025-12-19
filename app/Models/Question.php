<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $fillable = [
        'question_set_id',
        'order',
        'question_text',
        'field_name',
        'validation_rule',
        'error_message',
        'is_required',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function validateAnswer(string $answer): array
    {
        $errors = [];

        if ($this->is_required && empty(trim($answer))) {
            $errors[] = $this->error_message ?? "Câu hỏi này là bắt buộc.";
            return ['valid' => false, 'errors' => $errors];
        }

        if (!empty($this->validation_rule)) {
            $valid = match($this->validation_rule) {
                'phone' => preg_match('/^0\d{9,10}$/', $answer),
                'email' => filter_var($answer, FILTER_VALIDATE_EMAIL) !== false,
                'numeric' => is_numeric($answer),
                'min:3' => strlen($answer) >= 3,
                'min:5' => strlen($answer) >= 5,
                'min:10' => strlen($answer) >= 10,
                default => true,
            };

            if (!$valid) {
                $errors[] = $this->error_message ?? "Giá trị không hợp lệ.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
