<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionSet extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_message',
        'completion_message',
        'completion_buttons',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'completion_buttons' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function getActiveQuestions()
    {
        return $this->questions()->where('is_required', true)->orWhere(function($query) {
            $query->where('is_required', false);
        })->orderBy('order')->get();
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)->orderBy('is_default', 'desc')->get();
    }
}
