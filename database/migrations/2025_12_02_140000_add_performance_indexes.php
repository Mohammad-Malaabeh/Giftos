<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPerformanceIndexes extends Migration
{
    /**
     * Check if an index exists on a table using raw SQL
     */
    protected function indexExists($table, $name)
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        // SQLite does not have information_schema; use PRAGMA instead
        if ($driver === 'sqlite') {
            try {
                $rows = DB::select("PRAGMA index_list('" . $table . "')");
                foreach ($rows as $row) {
                    // PRAGMA index_list returns 'name' or 'idx' depending on SQLite version
                    $idxName = $row->name ?? $row->idx ?? null;
                    if ($idxName === $name) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                return false;
            }

            return false;
        }

        // Fallback for other drivers (MySQL expected): query information_schema
        $databaseName = $connection->getDatabaseName();
        return DB::table('information_schema.statistics')
                ->where('table_schema', $databaseName)
                ->where('table_name', $table)
                ->where('index_name', $name)
                ->exists();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Helper function to safely add index if it doesn't exist
        $addIndexIfNotExists = function ($table, $indexName, $columns) {
            if (!$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $table) use ($indexName, $columns) {
                    if (is_array($columns)) {
                        $table->index($columns, $indexName);
                    } else {
                        $table->index($columns, $indexName);
                    }
                });
                return true;
            }
            return false;
        };

        // Products table indexes - Most critical for performance
        $addIndexIfNotExists('products', 'idx_products_status', 'status');
        $addIndexIfNotExists('products', 'idx_products_featured', 'featured');
        $addIndexIfNotExists('products', 'idx_products_price', 'price');
        $addIndexIfNotExists('products', 'idx_products_stock', 'stock');
        $addIndexIfNotExists('products', 'idx_products_views', 'views');
        $addIndexIfNotExists('products', 'idx_products_category_status', ['category_id', 'status']);

        // Orders table indexes
        $addIndexIfNotExists('orders', 'idx_orders_user_id', 'user_id');
        $addIndexIfNotExists('orders', 'idx_orders_status', 'status');
        $addIndexIfNotExists('orders', 'idx_orders_payment_status', 'payment_status');
        $addIndexIfNotExists('orders', 'idx_orders_created_at', 'created_at');
        $addIndexIfNotExists('orders', 'idx_orders_user_status', ['user_id', 'status']);

        // Reviews table indexes
        $addIndexIfNotExists('reviews', 'idx_reviews_product_id', 'product_id');
        $addIndexIfNotExists('reviews', 'idx_reviews_approved', 'approved');
        $addIndexIfNotExists('reviews', 'idx_reviews_rating', 'rating');

        // Cart items table indexes
        $addIndexIfNotExists('cart_items', 'idx_cart_items_user_product', ['user_id', 'product_id']);

        // Activity logs table indexes
        $addIndexIfNotExists('activity_logs', 'idx_activity_logs_created_at', 'created_at');
        // activity_logs uses `subject_type` (not `type`) to reference the related model
        $addIndexIfNotExists('activity_logs', 'idx_activity_logs_subject_type', 'subject_type');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Helper function to safely drop index if it exists
        $dropIndexIfExists = function ($table, $indexName) {
            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
                return true;
            }
            return false;
        };

        // Drop products table indexes
        $dropIndexIfExists('products', 'idx_products_status');
        $dropIndexIfExists('products', 'idx_products_featured');
        $dropIndexIfExists('products', 'idx_products_price');
        $dropIndexIfExists('products', 'idx_products_stock');
        $dropIndexIfExists('products', 'idx_products_views');
        $dropIndexIfExists('products', 'idx_products_category_status');

        // Drop orders table indexes
        $dropIndexIfExists('orders', 'idx_orders_user_id');
        $dropIndexIfExists('orders', 'idx_orders_status');
        $dropIndexIfExists('orders', 'idx_orders_payment_status');
        $dropIndexIfExists('orders', 'idx_orders_created_at');
        $dropIndexIfExists('orders', 'idx_orders_user_status');

        // Drop reviews table indexes
        $dropIndexIfExists('reviews', 'idx_reviews_product_id');
        $dropIndexIfExists('reviews', 'idx_reviews_approved');
        $dropIndexIfExists('reviews', 'idx_reviews_rating');

        // Drop cart_items table indexes
        $dropIndexIfExists('cart_items', 'idx_cart_items_user_product');

        // Drop activity_logs table indexes
        $dropIndexIfExists('activity_logs', 'idx_activity_logs_created_at');
        $dropIndexIfExists('activity_logs', 'idx_activity_logs_subject_type');
    }
}
