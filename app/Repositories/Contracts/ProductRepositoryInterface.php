<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
    public function findByCategory(int $categoryId);
    public function search(string $query);
    public function getFeatured(int $limit = 8);
    public function getLatest(int $limit = 12);
    public function getOnSale();
    public function getInStock();
    public function getOutOfStock();
    public function getByPriceRange(float $min, float $max);
    public function getActive();
    public function getInactive();
    public function withVariants();
    public function withCategories();
    public function withReviews();
    public function withAverageRating();
    public function incrementViews(int $productId);

    // Chainable methods (via __call forwarding to Builder)
    public function featured();
    public function active();
    public function onSale();
}
