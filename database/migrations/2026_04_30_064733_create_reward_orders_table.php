<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_orders', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('reward_id')->constrained('rewards')->cascadeOnDelete();

    $table->integer('points_spent')->default(0);
    $table->string('status')->default('new');
    $table->text('comment')->nullable();

    $table->timestamps();

    $table->index('status');
    $table->index(['user_id', 'status']);
    $table->index(['status', 'created_at']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_orders');
    }
};