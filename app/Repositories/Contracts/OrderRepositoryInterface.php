<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUser(int $userId);
    public function findByStatus(string $status);
    public function withItems();
    public function withUser();
    public function withShippingAddress();
    public function getPending();
    public function getProcessing();
    public function getCompleted();
    public function getCancelled();
    public function getByDateRange(string $startDate, string $endDate);
    public function getTotalRevenue(string $startDate = null, string $endDate = null);
    public function getOrderCount(string $startDate = null, string $endDate = null);
    public function getAverageOrderValue(string $startDate = null, string $endDate = null);
}
