<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection;

    /**
     * Get inactive users
     *
     * @return Collection
     */
    public function getInactiveUsers(): Collection;

    /**
     * Search users by name or email
     *
     * @param string $keyword
     * @return Collection
     */
    public function search(string $keyword): Collection;
}
