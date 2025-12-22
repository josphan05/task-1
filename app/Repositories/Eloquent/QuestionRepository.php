<?php

namespace App\Repositories\Eloquent;

use App\Models\Question;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function model(): string
    {
        return Question::class;
    }

    public function getByQuestionSetOrdered(int|string $questionSetId): Collection
    {
        return $this->where('question_set_id', $questionSetId)
            ->orderBy('order')
            ->all();
    }

    public function getWithQuestionSet(int|string $id): ?Question
    {
        return $this->with('questionSet')->find($id);
    }
}

