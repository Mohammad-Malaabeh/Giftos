<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('label')->nullable(); // e.g., Home, Work
            $table->string('name');
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city');
            $table->string('zip', 20);
            $table->string('country', 2);

            $table->boolean('is_default_shipping')->default(false)->index();
            $table->boolean('is_default_billing')->default(false)->index();

            $table->timestamps();

            // Composite index
            $table->index(
                ['user_id', 'is_default_shipping', 'is_default_billing'],
                'ua_user_defaults_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
