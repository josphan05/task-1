<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Container\Container as Application;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Application
     */
    protected Application $app;

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * BaseRepository constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
        $this->resetQuery();
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * Make Model instance
     *
     * @return Model
     */
    protected function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Reset query builder
     *
     * @return void
     */
    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    /**
     * Execute query and reset
     *
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        $query = $this->query;
        $this->resetQuery();
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->getQuery()->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->getQuery()->paginate($perPage, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->getQuery()->find($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->getQuery()->findOrFail($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->getQuery()->where($field, $value)->first($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByField(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->getQuery()->where($field, $value)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWhere(array $where, array $columns = ['*']): Collection
    {
        return $this->getQuery()->where($where)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*']): Collection
    {
        return $this->getQuery()->whereIn($field, $values)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(int|string $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(int|string $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function with(array|string $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->getQuery()->count();
    }

    /**
     * Add a basic where clause to the query
     *
     * @param string|array $column
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function where(string|array $column, mixed $operator = null, mixed $value = null): self
    {
        $this->query->where($column, $operator, $value);
        return $this;
    }

    /**
     * Add an "or where" clause to the query
     *
     * @param string|array $column
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function orWhere(string|array $column, mixed $operator = null, mixed $value = null): self
    {
        $this->query->orWhere($column, $operator, $value);
        return $this;
    }

    /**
     * Get first record
     *
     * @param array $columns
     * @return Model|null
     */
    public function first(array $columns = ['*']): ?Model
    {
        return $this->getQuery()->first($columns);
    }

    /**
     * Get first record or fail
     *
     * @param array $columns
     * @return Model
     */
    public function firstOrFail(array $columns = ['*']): Model
    {
        return $this->getQuery()->firstOrFail($columns);
    }

    /**
     * Update or create a record
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * Delete records by given criteria
     *
     * @param array $where
     * @return int
     */
    public function deleteWhere(array $where): int
    {
        return $this->model->where($where)->delete();
    }
}

