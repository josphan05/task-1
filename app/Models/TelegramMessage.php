<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramMessage extends Model
{
    protected $fillable = [
        'message_id',
        'text',
        'telegram_user_id',
        'telegram_username',
        'telegram_first_name',
        'telegram_last_name',
        'user_id',
        'chat_id',
        'reply_to_message_id',
        'raw_data',
        'is_completed',
        'telegram_conversation_id',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    /**
     * Get the linked user (if any)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get full name of telegram user
     */
    public function getTelegramFullNameAttribute(): string
    {
        return trim(($this->telegram_first_name ?? '') . ' ' . ($this->telegram_last_name ?? '')) ?: 'Unknown';
    }

    /**
     * Get display name (username or full name)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->telegram_username) {
            return '@' . $this->telegram_username;
        }
        return $this->telegram_full_name;
    }
}
