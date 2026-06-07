<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('partner_name')->nullable();
    $table->text('description')->nullable();
    $table->text('image')->nullable();
    $table->integer('price_points')->default(0);
    $table->integer('stock')->default(0);
    $table->string('category')->default('other');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('is_active');
    $table->index('category');
    $table->index(['is_active', 'category']);
    $table->index('price_points');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};