<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
    $table->id();

    $table->string('title');
    $table->text('description')->nullable();

    $table->string('status')->default('open')->index();

    $table->foreignId('assigned_to')
        ->nullable()
        ->constrained('users')
        ->nullOnDelete();

    $table->integer('points')->default(10);
    $table->text('photo')->nullable();

    $table->timestamps();

    $table->index(['status', 'assigned_to']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};