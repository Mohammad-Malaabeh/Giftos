<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrderByScope implements Scope
{
    public function __construct(
        private readonly string $column,
        private readonly string $direction = 'desc'
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy($this->column, $this->direction);
    }
}

class LatestScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->latest();
    }
}

class OldestScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->oldest();
    }
}

class PopularScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($model->getTable() === 'products') {
            $builder->orderByDesc('views');
        } elseif ($model->getTable() === 'orders') {
            $builder->orderByDesc('total');
        } else {
            $builder->latest();
        }
    }
}

class PriceScope implements Scope
{
    public function __construct(
        private readonly string $direction = 'asc'
    ) {}

    public function apply(Builder $builder, Model $model): void
    {
        if ($model->getTable() === 'products') {
            $builder->orderBy('price', $this->direction);
        }
    }
}

class NameScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->orderBy('name');
    }
}
