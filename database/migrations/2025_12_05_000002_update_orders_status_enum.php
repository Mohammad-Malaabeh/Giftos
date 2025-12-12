<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't enforce enums strictly
            // Just update the data
            DB::table('orders')
                ->where('status', 'canceled')
                ->update(['status' => 'cancelled']);
        } else {
            // For MySQL/PostgreSQL - CRITICAL ORDER:

            // Step 1: ADD 'cancelled' to enum (keep 'canceled' temporarily)
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'paid', 'shipped', 'delivered', 'completed', 'canceled', 'cancelled', 'refunded') DEFAULT 'pending'");

            // Step 2: Now update data from 'canceled' to 'cancelled'
            DB::table('orders')
                ->where('status', 'canceled')
                ->update(['status' => 'cancelled']);

            // Step 3: Remove 'canceled' from enum (only 'cancelled' remains)
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'paid', 'shipped', 'delivered', 'completed', 'cancelled', 'refunded') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver !== 'sqlite') {
            // Revert: add 'canceled' back, update data, remove 'cancelled'
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'shipped', 'completed', 'canceled', 'cancelled', 'refunded') DEFAULT 'pending'");

            DB::table('orders')
                ->where('status', 'cancelled')
                ->update(['status' => 'canceled']);

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'shipped', 'completed', 'canceled', 'refunded') DEFAULT 'pending'");
        }
    }
};
