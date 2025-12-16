<?php

namespace App\Repositories\Eloquent;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function model(): string
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findByField('email', $email);
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', UserStatus::ACTIVE)->get();
    }

    public function getInactiveUsers(): Collection
    {
        return $this->model->where('status', UserStatus::INACTIVE)->get();
    }

    public function search(string $keyword): Collection
    {
        return $this->model
            ->where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->get();
    }
}
