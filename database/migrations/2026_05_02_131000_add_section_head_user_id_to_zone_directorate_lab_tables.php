<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table): void {
            if (!Schema::hasColumn('zones', 'section_head_user_id')) {
                $table->uuid('section_head_user_id')->nullable()->after('inventory_location_id');
                $table->foreign('section_head_user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('directorates', function (Blueprint $table): void {
            if (!Schema::hasColumn('directorates', 'section_head_user_id')) {
                $table->uuid('section_head_user_id')->nullable()->after('head_id');
                $table->foreign('section_head_user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('labs', function (Blueprint $table): void {
            if (!Schema::hasColumn('labs', 'section_head_user_id')) {
                $table->uuid('section_head_user_id')->nullable()->after('manager_id');
                $table->foreign('section_head_user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('labs', function (Blueprint $table): void {
            if (Schema::hasColumn('labs', 'section_head_user_id')) {
                $table->dropForeign(['section_head_user_id']);
                $table->dropColumn('section_head_user_id');
            }
        });

        Schema::table('directorates', function (Blueprint $table): void {
            if (Schema::hasColumn('directorates', 'section_head_user_id')) {
                $table->dropForeign(['section_head_user_id']);
                $table->dropColumn('section_head_user_id');
            }
        });

        Schema::table('zones', function (Blueprint $table): void {
            if (Schema::hasColumn('zones', 'section_head_user_id')) {
                $table->dropForeign(['section_head_user_id']);
                $table->dropColumn('section_head_user_id');
            }
        });
    }
};
