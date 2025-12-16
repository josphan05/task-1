<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find record by id
     *
     * @param int|string $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int|string $id, array $columns = ['*']): ?Model;

    /**
     * Find record by id or fail
     *
     * @param int|string $id
     * @param array $columns
     * @return Model
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model;

    /**
     * Find record by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Find records by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Collection
     */
    public function findAllByField(string $field, mixed $value, array $columns = ['*']): Collection;

    /**
     * Find records by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @return Collection
     */
    public function findWhere(array $where, array $columns = ['*']): Collection;

    /**
     * Find records where field is in array
     *
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return Collection
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*']): Collection;

    /**
     * Add a basic where clause to the query.
     *
     * @param string|array $column
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function where(string|array $column, mixed $operator = null, mixed $value = null): self;

    /**
     * Add an "or where" clause to the query.
     *
     * @param string|array $column
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function orWhere(string|array $column, mixed $operator = null, mixed $value = null): self;

    /**
     * Create new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update record
     *
     * @param int|string $id
     * @param array $data
     * @return Model
     */
    public function update(int|string $id, array $data): Model;

    /**
     * Delete record
     *
     * @param int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Order by field
     *
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function orderBy(string $column, string $direction = 'asc'): self;

    /**
     * Load relations
     *
     * @param array|string $relations
     * @return self
     */
    public function with(array|string $relations): self;

    /**
     * Count records
     *
     * @return int
     */
    public function count(): int;
}

