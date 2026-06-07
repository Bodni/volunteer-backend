<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adoption_requests', function (Blueprint $table) {
    $table->id();
    $table->string('animal_name');
    $table->string('name');
    $table->string('phone');
    $table->text('message')->nullable();
    $table->string('status')->default('new');
    $table->timestamps();

    $table->index('status');
    $table->index('created_at');
    $table->index(['status', 'created_at']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('adoption_requests');
    }
};