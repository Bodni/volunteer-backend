<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('found_requests', function (Blueprint $table) {
    $table->id();
    $table->string('city');
    $table->string('address');
    $table->text('description')->nullable();
    $table->string('status')->default('new');
    $table->timestamps();

    $table->index('city');
    $table->index('status');
    $table->index(['status', 'city']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('found_requests');
    }
};