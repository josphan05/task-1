<?php

namespace App\Repositories\Contracts;

use App\Models\QuestionSet;
use Illuminate\Database\Eloquent\Collection;

interface QuestionSetRepositoryInterface extends RepositoryInterface
{
    /**
     * Get question sets with questions ordered
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getWithQuestions(int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Get question set with ordered questions
     *
     * @param int|string $id
     * @return QuestionSet|null
     */
    public function getWithOrderedQuestions(int|string $id): ?QuestionSet;

    /**
     * Get active question sets
     *
     * @return Collection
     */
    public function getActive(): Collection;

    /**
     * Get default question set
     *
     * @return QuestionSet|null
     */
    public function getDefault(): ?QuestionSet;

    /**
     * Unset default for all question sets except given id
     *
     * @param int|string|null $exceptId
     * @return int Number of updated records
     */
    public function unsetDefault(int|string|null $exceptId = null): int;
}

