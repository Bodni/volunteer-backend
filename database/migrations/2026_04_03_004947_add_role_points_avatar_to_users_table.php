<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'role')) {
            $table->string('role')->default('user')->after('password')->index();
        }

        if (!Schema::hasColumn('users', 'points')) {
            $table->integer('points')->default(0)->after('role');
        }

        if (!Schema::hasColumn('users', 'avatar')) {
            $table->text('avatar')->nullable()->after('points');
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (Schema::hasColumn('users', 'avatar')) {
            $table->dropColumn('avatar');
        }

        if (Schema::hasColumn('users', 'points')) {
            $table->dropColumn('points');
        }

        if (Schema::hasColumn('users', 'role')) {
            $table->dropColumn('role');
        }
    });
}
};
