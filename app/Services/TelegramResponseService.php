<?php

namespace App\Services;

use App\Repositories\Contracts\TelegramCallbackRepositoryInterface;
use App\Repositories\Contracts\TelegramMessageRepositoryInterface;

class TelegramResponseService
{
    public function __construct(
        protected TelegramCallbackRepositoryInterface $callbackRepository,
        protected TelegramMessageRepositoryInterface $messageRepository
    ) {}

    /**
     * Filter out empty button rows and buttons
     *
     * @param array|null $buttons
     * @return array|null
     */
    public function filterEmptyButtons(?array $buttons): ?array
    {
        if (empty($buttons)) {
            return null;
        }

        $filtered = [];

        foreach ($buttons as $row) {
            $filteredRow = [];

            foreach ($row as $button) {
                if (!empty($button['text']) && !empty($button['value'])) {
                    $filteredRow[] = $button;
                }
            }

            if (!empty($filteredRow)) {
                $filtered[] = $filteredRow;
            }
        }

        return !empty($filtered) ? $filtered : null;
    }

    /**
     * Get callbacks grouped by message ID
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getCallbacksGrouped(int $limit = 100): \Illuminate\Support\Collection
    {
        return $this->callbackRepository->getGroupedByMessageId($limit);
    }

    /**
     * Get messages grouped by reply to
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getMessagesGrouped(int $limit = 100): \Illuminate\Support\Collection
    {
        return $this->messageRepository->getGroupedByReplyTo($limit);
    }

    /**
     * Get latest callback ID
     *
     * @param \Illuminate\Support\Collection $callbacksGrouped
     * @return int
     */
    public function getLatestCallbackId(\Illuminate\Support\Collection $callbacksGrouped): int
    {
        return $callbacksGrouped->flatten()->max('id') ?? 0;
    }

    /**
     * Get latest message ID
     *
     * @param \Illuminate\Support\Collection $messagesGrouped
     * @return int
     */
    public function getLatestMessageId(\Illuminate\Support\Collection $messagesGrouped): int
    {
        return $messagesGrouped->flatten()->max('id') ?? 0;
    }
}

