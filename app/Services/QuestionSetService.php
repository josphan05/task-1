<?php

namespace App\Services;

use App\Models\QuestionSet;
use App\Repositories\Contracts\QuestionSetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class QuestionSetService extends BaseService
{
    public function __construct(
        protected QuestionSetRepositoryInterface $questionSetRepository
    ) {
        $this->repository = $questionSetRepository;
    }

    /**
     * Get paginated question sets with questions
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithQuestions(int $perPage = 10): LengthAwarePaginator
    {
        return $this->questionSetRepository->getWithQuestions($perPage);
    }

    /**
     * Get question set with ordered questions
     *
     * @param int|string $id
     * @return QuestionSet|null
     */
    public function getWithOrderedQuestions(int|string $id): ?QuestionSet
    {
        return $this->questionSetRepository->getWithOrderedQuestions($id);
    }

    /**
     * Create question set and handle default flag
     *
     * @param array $data
     * @return QuestionSet
     */
    public function createQuestionSet(array $data): QuestionSet
    {
        // Nếu set làm mặc định, bỏ mặc định của các bộ khác
        if ($data['is_default'] ?? false) {
            $this->questionSetRepository->unsetDefault();
        }

        return $this->repository->create($data);
    }

    /**
     * Update question set and handle default flag
     *
     * @param int|string $id
     * @param array $data
     * @return QuestionSet
     */
    public function updateQuestionSet(int|string $id, array $data): QuestionSet
    {
        // Nếu set làm mặc định, bỏ mặc định của các bộ khác
        if ($data['is_default'] ?? false) {
            $this->questionSetRepository->unsetDefault($id);
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Get active question sets
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->questionSetRepository->getActive();
    }

    /**
     * Get default question set
     *
     * @return QuestionSet|null
     */
    public function getDefault(): ?QuestionSet
    {
        return $this->questionSetRepository->getDefault();
    }
}

