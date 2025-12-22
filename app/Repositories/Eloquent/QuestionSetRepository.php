<?php

namespace App\Repositories\Eloquent;

use App\Models\QuestionSet;
use App\Repositories\Contracts\QuestionSetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class QuestionSetRepository extends BaseRepository implements QuestionSetRepositoryInterface
{
    public function model(): string
    {
        return QuestionSet::class;
    }

    public function getWithQuestions(int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->with('questions')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getWithOrderedQuestions(int|string $id): ?QuestionSet
    {
        return $this->with(['questions' => function($query) {
            $query->orderBy('order');
        }])->find($id);
    }

    public function getActive(): Collection
    {
        return $this->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->all();
    }

    public function getDefault(): ?QuestionSet
    {
        return $this->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public function unsetDefault(int|string $exceptId = null): int
    {
        $query = $this->model->where('is_default', true);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->update(['is_default' => false]);
    }
}

