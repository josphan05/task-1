<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionSetCommand extends Model
{
    protected $fillable = [
        'command',
        'question_set_id',
        'response_message',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public static function findByCommand(string $command): ?self
    {
        return self::where('command', $command)
            ->where('is_active', true)
            ->with('questionSet')
            ->first();
    }
}
