<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;
    protected Builder $query;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->resetQuery();
    }

    public function all(array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query->get($columns);
    }

    public function find($id, array $columns = ['*']): ?Model
    {
        return $this->query->find($id, $columns);
    }

    public function findBy(string $field, $value, array $columns = ['*']): ?Model
    {
        return $this->query->where($field, $value)->first($columns);
    }

    public function findMany(array $ids, array $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->query->whereIn('id', $ids)->get($columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): Model
    {
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }

    public function delete($id): bool
    {
        return $this->find($id)->delete();
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query->paginate($perPage, $columns);
    }

    public function with(array $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    public function where(string $column, $operator, $value = null): self
    {
        if (func_num_args() === 2) {
            $this->query->where($column, $operator);
        } else {
            $this->query->where($column, $operator, $value);
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    public function latest(string $column = 'created_at'): self
    {
        $this->query->latest($column);
        return $this;
    }

    public function oldest(string $column = 'created_at'): self
    {
        $this->query->oldest($column);
        return $this;
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function exists(): bool
    {
        return $this->query->exists();
    }

    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function __call(string $method, array $parameters)
    {
        $result = $this->query->$method(...$parameters);
        
        if ($result instanceof Builder) {
            $this->query = $result;
            return $this;
        }
        
        return $result;
    }
}
