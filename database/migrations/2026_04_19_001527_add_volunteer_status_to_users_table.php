<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    if (!Schema::hasColumn('users', 'volunteer_status')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('volunteer_status')
                ->default('free')
                ->after('avatar')
                ->index();
        });
    }
}

public function down(): void
{
    if (Schema::hasColumn('users', 'volunteer_status')) {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('volunteer_status');
        });
    }
}
};