<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained()->onDelete('set null');
            
            // Quantity and pricing
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->comment('Price at time of purchase');
            $table->decimal('sale_price', 10, 2)->nullable()->comment('Sale price at time of purchase');
            $table->decimal('total', 10, 2)->comment('Line total (price * quantity)');
            
            // Product snapshot at time of purchase
            $table->string('product_name')->comment('Product name snapshot');
            $table->string('product_sku')->nullable()->comment('Product SKU snapshot');
            $table->string('variant_name')->nullable()->comment('Variant name snapshot');
            $table->json('variant_attributes')->nullable()->comment('Variant attributes snapshot');
            
            // Status and tracking
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'product_id']);
            $table->index(['product_id', 'status']);
            $table->index(['order_id', 'status']);
            
            // Unique constraint to prevent duplicate items in same order
            $table->unique(['order_id', 'product_id', 'variant_id'], 'order_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_product');
    }
};
