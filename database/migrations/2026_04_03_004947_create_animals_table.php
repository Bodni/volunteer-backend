<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('species');
    $table->string('age');
    $table->string('city');
    $table->string('status')->default('looking_home');
    $table->text('description')->nullable();
    $table->text('photo')->nullable();
    $table->timestamps();

    $table->index('status');
    $table->index('species');
    $table->index('city');
    $table->index(['status', 'species']);
    $table->index(['status', 'created_at']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};