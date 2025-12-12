<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Assignment metadata
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['permission_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['permission_id', 'is_active']);
            $table->index(['expires_at']);
            
            // Unique constraint to prevent duplicate permission assignments
            $table->unique(['permission_id', 'user_id'], 'permission_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
