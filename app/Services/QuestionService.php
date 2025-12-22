<?php

namespace App\Services;

use App\Models\Question;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class QuestionService extends BaseService
{
    public function __construct(
        protected QuestionRepositoryInterface $questionRepository
    ) {
        $this->repository = $questionRepository;
    }

    /**
     * Parse options from text format "Text|Value" to array
     *
     * @param string|null $optionsText
     * @return array|null
     */
    public function parseOptions(?string $optionsText): ?array
    {
        if (empty($optionsText)) {
            return null;
        }

        $options = [];
        $lines = explode("\n", $optionsText);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode('|', $line, 2);
            $text = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : $text;

            if (!empty($text)) {
                $options[] = [
                    'text' => $text,
                    'value' => $value,
                ];
            }
        }

        return !empty($options) ? $options : null;
    }

    /**
     * Create question with parsed options
     *
     * @param array $data
     * @return Question
     */
    public function createQuestion(array $data): Question
    {
        // Parse options từ text format "Text|Value" thành array
        if (!empty($data['options'])) {
            $data['options'] = $this->parseOptions($data['options']);
        } else {
            $data['options'] = null;
        }

        return $this->repository->create($data);
    }

    /**
     * Update question with parsed options
     *
     * @param int|string $id
     * @param array $data
     * @return Question
     */
    public function updateQuestion(int|string $id, array $data): Question
    {
        // Parse options từ text format "Text|Value" thành array
        if (!empty($data['options'])) {
            $data['options'] = $this->parseOptions($data['options']);
        } else {
            $data['options'] = null;
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Get questions by question set id ordered
     *
     * @param int|string $questionSetId
     * @return Collection
     */
    public function getByQuestionSetOrdered(int|string $questionSetId): Collection
    {
        return $this->questionRepository->getByQuestionSetOrdered($questionSetId);
    }
}

