<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // User relationship
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Order identity
            $table->string('number')->unique();
            $table->enum('status', [
                'pending', 'processing', 'paid', 'shipped', 'delivered',
                'completed', 'cancelled', 'refunded'
            ])->default('pending')->index();

            // Money
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // Payment
            $table->string('payment_method', 30)->default('cod');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded', 'failed'])
                ->default('unpaid')
                ->index();
            $table->string('transaction_id')->nullable()->index();

            // Addresses
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();

            // Timeline
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Composite index for dashboard performance
            $table->index(['user_id', 'status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
