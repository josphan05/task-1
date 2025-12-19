<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramConversation extends Model
{
    protected $fillable = [
        'telegram_user_id',
        'question_set_id',
        'step',
        'current_question_order',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'current_question_order' => 'integer',
    ];

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function updateStep(?string $step = null, ?int $questionOrder = null, ?array $data = null): void
    {
        if ($step !== null) {
            $this->step = $step;
        }
        if ($questionOrder !== null) {
            $this->current_question_order = $questionOrder;
        }
        if ($data !== null) {
            $currentData = $this->data ?? [];
            $this->data = array_merge($currentData, $data);
        }
        $this->save();
    }

    public function reset(): void
    {
        $this->step = null;
        $this->current_question_order = null;
        $this->data = null;
        $this->question_set_id = null;
        $this->save();
    }
}

