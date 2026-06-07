<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('found_requests', function (Blueprint $table) {
            $table->text('photo')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('found_requests', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};