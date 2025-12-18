<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface TelegramMessageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get messages with user relationship, ordered by created_at desc
     */
    public function getWithUser(int $limit = 50): Collection;

    /**
     * Get messages with user relationship, paginated
     */
    public function paginateWithUser(int $perPage = 20): LengthAwarePaginator;

    /**
     * Get new messages since a specific ID
     */
    public function getNewSince(int $sinceId): Collection;

    /**
     * Get messages grouped by reply_to_message_id
     */
    public function getGroupedByReplyTo(int $limit = 50): Collection;

    /**
     * Find user by telegram_id
     */
    public function findUserByTelegramId(string $telegramId): ?Model;
}

