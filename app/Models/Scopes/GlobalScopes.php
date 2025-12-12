<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('status', true);
    }
}

class PublishedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('published', true);
    }
}

class InStockScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('stock', '>', 0);
    }
}

class FeaturedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('featured', true);
    }
}

class OnSaleScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotNull('sale_price')->where('sale_price', '>', 0);
    }
}

class VerifiedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->whereNotNull('email_verified_at');
    }
}

class ApprovedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('approved', true);
    }
}

class HighRatedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($model->getTable() === 'products') {
            $builder->whereHas('reviews', function (Builder $query) {
                $query->havingRaw('AVG(rating) >= 4.0');
            });
        }
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

class RecentScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('created_at', '>=', now()->subDays(30));
    }
}
