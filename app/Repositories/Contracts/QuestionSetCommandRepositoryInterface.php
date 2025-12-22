<?php

namespace App\Repositories\Contracts;

use App\Models\QuestionSetCommand;
use Illuminate\Database\Eloquent\Collection;

interface QuestionSetCommandRepositoryInterface extends RepositoryInterface
{
    /**
     * Get commands with question set
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getWithQuestionSet(int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator;

    /**
     * Find command by command string
     *
     * @param string $command
     * @return QuestionSetCommand|null
     */
    public function findByCommand(string $command): ?QuestionSetCommand;

    /**
     * Get active question sets for command assignment
     *
     * @return Collection
     */
    public function getActiveQuestionSets(): Collection;
}

