<?php

namespace App\Repositories\Eloquent;

use App\Models\TelegramCallback;
use App\Models\User;
use App\Repositories\Contracts\TelegramCallbackRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TelegramCallbackRepository extends BaseRepository implements TelegramCallbackRepositoryInterface
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return TelegramCallback::class;
    }

    /**
     * Get callbacks with user relationship, ordered by created_at desc
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
     * Get callbacks with user relationship, paginated
     */
    public function paginateWithUser(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get callbacks grouped by message_id, sorted by latest callback in each group
     */
    public function getGroupedByMessageId(int $limit = 100): Collection
    {
        // Lấy tất cả callbacks, không filter theo loại
        $callbacks = $this->model
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        // Group by message_id, sử dụng 'no_message' cho các callback không có message_id
        $grouped = $callbacks->groupBy(function ($callback) {
            return $callback->message_id ?? 'no_message_' . $callback->id;
        });

        // Sort groups by the latest callback's created_at in each group
        return $grouped->sortByDesc(function ($group) {
            return $group->first()->created_at;
        });
    }

    /**
     * Get new callbacks since a specific ID
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

