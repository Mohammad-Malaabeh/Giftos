<?php

namespace App\Repositories\Contracts;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']);
    public function find($id, array $columns = ['*']);
    public function findBy(string $field, $value, array $columns = ['*']);
    public function findMany(array $ids, array $columns = ['*']);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function paginate(int $perPage = 15, array $columns = ['*']);
    public function with(array $relations);
    public function where(string $column, $operator, $value = null);
    public function orderBy(string $column, string $direction = 'asc');
    public function latest(string $column = 'created_at');
    public function oldest(string $column = 'created_at');
    public function count();
    public function exists();
}
