<?php

namespace App\Repositories\Eloquent;

use App\Models\QuestionSet;
use App\Models\QuestionSetCommand;
use App\Repositories\Contracts\QuestionSetCommandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class QuestionSetCommandRepository extends BaseRepository implements QuestionSetCommandRepositoryInterface
{
    public function model(): string
    {
        return QuestionSetCommand::class;
    }

    public function getWithQuestionSet(int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->with('questionSet')
            ->orderBy('command')
            ->paginate($perPage);
    }

    public function findByCommand(string $command): ?QuestionSetCommand
    {
        return $this->where('command', $command)
            ->where('is_active', true)
            ->with('questionSet')
            ->first();
    }

    public function getActiveQuestionSets(): Collection
    {
        return QuestionSet::where('is_active', true)->get();
    }
}

