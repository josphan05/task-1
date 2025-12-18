<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface TelegramCallbackRepositoryInterface extends RepositoryInterface
{
    /**
     * Get callbacks with user relationship, ordered by created_at desc
     */
    public function getWithUser(int $limit = 50): Collection;

    /**
     * Get callbacks with user relationship, paginated
     */
    public function paginateWithUser(int $perPage = 20): LengthAwarePaginator;

    /**
     * Get new callbacks since a specific ID
     */
    public function getNewSince(int $sinceId): Collection;

    /**
     * Get callbacks grouped by message_id
     */
    public function getGroupedByMessageId(int $limit = 50): Collection;

    /**
     * Find user by telegram_id
     */
    public function findUserByTelegramId(string $telegramId): ?Model;
}

