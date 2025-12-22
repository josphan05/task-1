<?php

namespace App\Services;

use App\Models\QuestionSetCommand;
use App\Repositories\Contracts\QuestionSetCommandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class QuestionSetCommandService extends BaseService
{
    public function __construct(
        protected QuestionSetCommandRepositoryInterface $commandRepository
    ) {
        $this->repository = $commandRepository;
    }

    /**
     * Get paginated commands with question set
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithQuestionSet(int $perPage = 10): LengthAwarePaginator
    {
        return $this->commandRepository->getWithQuestionSet($perPage);
    }

    /**
     * Normalize command string (ensure it starts with /)
     *
     * @param string $command
     * @return string
     */
    public function normalizeCommand(string $command): string
    {
        if (!str_starts_with($command, '/')) {
            return '/' . $command;
        }
        return $command;
    }

    /**
     * Create command with normalized command string
     *
     * @param array $data
     * @return QuestionSetCommand
     */
    public function createCommand(array $data): QuestionSetCommand
    {
        $data['command'] = $this->normalizeCommand($data['command']);
        return $this->repository->create($data);
    }

    /**
     * Update command with normalized command string
     *
     * @param int|string $id
     * @param array $data
     * @return QuestionSetCommand
     */
    public function updateCommand(int|string $id, array $data): QuestionSetCommand
    {
        $data['command'] = $this->normalizeCommand($data['command']);
        return $this->repository->update($id, $data);
    }

    /**
     * Find command by command string
     *
     * @param string $command
     * @return QuestionSetCommand|null
     */
    public function findByCommand(string $command): ?QuestionSetCommand
    {
        return $this->commandRepository->findByCommand($command);
    }

    /**
     * Get active question sets for command assignment
     *
     * @return Collection
     */
    public function getActiveQuestionSets(): Collection
    {
        return $this->commandRepository->getActiveQuestionSets();
    }
}

