<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 36)->unique();
            $table->string('method', 10);
            $table->string('uri');
            $table->integer('status');
            $table->float('duration_ms');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('method');
            $table->index('status');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['method', 'uri']);
            $table->index(['status', 'duration_ms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_logs');
    }
};
