<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            $table->string('sku')->nullable()->index();
            $table->decimal('price', 10, 2)->nullable(); // null => use product price
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('backorder_allowed')->default(false)->index();
            $table->date('backorder_eta')->nullable();

            $table->json('options')->nullable(); // e.g., {"Color":"Red","Size":"M"}
            $table->boolean('status')->default(true)->index();

            $table->timestamps();

            $table->unique(['product_id', 'sku']);
        });

        Schema::create('variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_id')->constrained()->cascadeOnDelete();
            $table->string('name');  // e.g., "Color"
            $table->string('value'); // e.g., "Red"
            $table->timestamps();
            $table->index(['variant_id', 'name']);
        });

        // cart_items: add variant_id
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')
                ->constrained('variants')->nullOnDelete();
        });

        // order_items: add variant fields
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')
                ->constrained('variants')->nullOnDelete();
            $table->json('variant_options')->nullable()->after('image_path'); // snapshot of options
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }
            if (Schema::hasColumn('order_items', 'variant_options')) {
                $table->dropColumn('variant_options');
            }
        });
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'variant_id')) {
                $table->dropConstrainedForeignId('variant_id');
            }
        });
        Schema::dropIfExists('variant_options');
        Schema::dropIfExists('variants');
    }
};
