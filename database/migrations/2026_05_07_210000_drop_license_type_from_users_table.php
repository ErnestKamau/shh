<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('users', 'license_type')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('license_type');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'license_type')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('license_type', 100)->default('shared_user');
            });
        }
    }
};
