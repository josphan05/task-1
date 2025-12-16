<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
        $this->repository = $userRepository;
    }

    /**
     * Get paginated users with optional filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Search by keyword
        if (!empty($filters['search'])) {
            $users = $this->userRepository->search($filters['search']);
            return new LengthAwarePaginator(
                $users->forPage(request()->get('page', 1), $perPage),
                $users->count(),
                $perPage,
                request()->get('page', 1),
                ['path' => request()->url()]
            );
        }

        // Filter by status
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'verified') {
                $users = $this->userRepository->getActiveUsers();
            } elseif ($filters['status'] === 'unverified') {
                $users = $this->userRepository->getInactiveUsers();
            } else {
                return $this->userRepository->orderBy('created_at', 'desc')->paginate($perPage);
            }

            return new LengthAwarePaginator(
                $users->forPage(request()->get('page', 1), $perPage),
                $users->count(),
                $perPage,
                request()->get('page', 1),
                ['path' => request()->url()]
            );
        }

        return $this->userRepository->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get recent users
     *
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getRecentUsers(int $limit = 5): LengthAwarePaginator
    {
        return $this->userRepository
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return $this->repository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telegram_id' => $data['telegram_id'] ?? null,
            'telegram_username' => $data['telegram_username'] ?? null,
            'status' => isset($data['status']) ? UserStatus::from($data['status']) : UserStatus::ACTIVE,
        ]);
    }

    /**
     * Update user
     *
     * @param int|string $id
     * @param array $data
     * @return User
     */
    public function updateUser(int|string $id, array $data): User
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'telegram_id' => $data['telegram_id'] ?? null,
            'telegram_username' => $data['telegram_username'] ?? null,
            'status' => isset($data['status']) ? UserStatus::from($data['status']) : UserStatus::ACTIVE,
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        return $this->repository->update($id, $updateData);
    }

    /**
     * Delete user
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteUser(int|string $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActiveUsers();
    }

    /**
     * Get inactive users
     *
     * @return Collection
     */
    public function getInactiveUsers(): Collection
    {
        return $this->userRepository->getInactiveUsers();
    }

    /**
     * Search users by name or email
     *
     * @param string $keyword
     * @return Collection
     */
    public function search(string $keyword): Collection
    {
        return $this->userRepository->search($keyword);
    }

    /**
     * Activate user
     *
     * @param int|string $id
     * @return User
     */
    public function activateUser(int|string $id): User
    {
        return $this->repository->update($id, [
            'status' => UserStatus::ACTIVE,
        ]);
    }

    /**
     * Deactivate user
     *
     * @param int|string $id
     * @return User
     */
    public function deactivateUser(int|string $id): User
    {
        return $this->repository->update($id, [
            'status' => UserStatus::INACTIVE,
        ]);
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'users' => $this->repository->count(),
            'verified' => $this->userRepository->getActiveUsers()->count(),
            'unverified' => $this->userRepository->getInactiveUsers()->count(),
            'today' => $this->userRepository->where('created_at', '>=', today())->count(),
        ];
    }
}
