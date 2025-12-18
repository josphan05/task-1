<?php

namespace App\Repositories\Eloquent;

use App\Models\TelegramMessage;
use App\Models\User;
use App\Repositories\Contracts\TelegramMessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TelegramMessageRepository extends BaseRepository implements TelegramMessageRepositoryInterface
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return TelegramMessage::class;
    }

    /**
     * Get messages with user relationship, ordered by created_at desc
     */
    public function getWithUser(int $limit = 50): Collection
    {
        return $this->model
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get messages with user relationship, paginated
     */
    public function paginateWithUser(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get messages grouped by reply_to_message_id, sorted by latest message in each group
     */
    public function getGroupedByReplyTo(int $limit = 50): Collection
    {
        $messages = $this->model
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->groupBy('reply_to_message_id');

        // Sort groups by the latest message's created_at in each group
        return $messages->sortByDesc(function ($group) {
            return $group->first()->created_at;
        });
    }

    /**
     * Get new messages since a specific ID
     */
    public function getNewSince(int $sinceId): Collection
    {
        return $this->model
            ->with('user')
            ->where('id', '>', $sinceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Find user by telegram_id
     */
    public function findUserByTelegramId(string $telegramId): ?Model
    {
        return User::where('telegram_id', $telegramId)->first();
    }
}

