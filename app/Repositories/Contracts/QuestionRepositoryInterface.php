<?php

namespace App\Repositories\Contracts;

use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

interface QuestionRepositoryInterface extends RepositoryInterface
{
    /**
     * Get questions by question set id ordered by order field
     *
     * @param int|string $questionSetId
     * @return Collection
     */
    public function getByQuestionSetOrdered(int|string $questionSetId): Collection;

    /**
     * Get question with question set
     *
     * @param int|string $id
     * @return Question|null
     */
    public function getWithQuestionSet(int|string $id): ?Question;
}

