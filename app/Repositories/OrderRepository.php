<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->query->where('user_id', $userId)->get();
    }

    public function findByStatus(string $status): Collection
    {
        return $this->query->where('status', $status)->get();
    }

    public function withItems(): self
    {
        $this->query->with(['items.product', 'items.variant']);
        return $this;
    }

    public function withUser(): self
    {
        $this->query->with('user');
        return $this;
    }

    public function withShippingAddress(): self
    {
        // Shipping address is a JSON column on the orders table, 
        // identifying the user address snapshot at the time of order.
        // No relationship needed.
        return $this;
    }

    public function getPending(): Collection
    {
        return $this->query->where('status', 'pending')->get();
    }


    public function getProcessing(): Collection
    {
        return $this->query->where('status', 'processing')->get();
    }

    public function getCompleted(): Collection
    {
        return $this->query->where('status', 'completed')->get();
    }

    public function getCancelled(): Collection
    {
        return $this->query->where('status', 'cancelled')->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->query->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getTotalRevenue(string $startDate = null, string $endDate = null): float
    {
        $query = $this->model->where('status', 'completed');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->sum('total');
    }

    public function getOrderCount(string $startDate = null, string $endDate = null): int
    {
        $query = $this->model;

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->count();
    }

    public function getAverageOrderValue(string $startDate = null, string $endDate = null): float
    {
        $query = $this->model->where('status', 'completed');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->avg('total') ?? 0;
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return $this->query->with(['user', 'items.product'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function updateStatus(int $orderId, string $status): Order
    {
        $order = $this->find($orderId);
        $order->status = $status;
        $order->save();

        return $order;
    }

    public function getOrdersWithItems(): Builder
    {
        return $this->query->with(['items.product', 'items.variant', 'user']);
    }
}
