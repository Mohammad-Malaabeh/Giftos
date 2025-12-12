<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            
            // Assignment metadata
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Temporary role assignment expiration');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'role_id']);
            $table->index(['role_id', 'is_active']);
            $table->index(['user_id', 'is_active']);
            
            // Unique constraint to prevent duplicate role assignments
            $table->unique(['user_id', 'role_id'], 'role_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
