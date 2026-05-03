<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_zone_relation')) {
            Schema::table('user_zone_relation', function (Blueprint $table): void {
                $table->dropUnique('user_zone_relation_user_id_unique');
                $table->unique(['user_id', 'zone_id'], 'user_zone_relation_user_id_zone_id_unique');
            });
        }

        if (Schema::hasTable('user_directorate_relation')) {
            Schema::table('user_directorate_relation', function (Blueprint $table): void {
                $table->dropUnique('user_directorate_relation_user_id_unique');
                $table->unique(['user_id', 'directorate_id'], 'user_directorate_relation_user_id_directorate_id_unique');
            });
        }

        if (Schema::hasTable('user_lab_relation')) {
            Schema::table('user_lab_relation', function (Blueprint $table): void {
                $table->dropUnique('user_lab_relation_user_id_unique');
                $table->unique(['user_id', 'lab_id'], 'user_lab_relation_user_id_lab_id_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_zone_relation')) {
            Schema::table('user_zone_relation', function (Blueprint $table): void {
                $table->dropUnique('user_zone_relation_user_id_zone_id_unique');
                $table->unique('user_id', 'user_zone_relation_user_id_unique');
            });
        }

        if (Schema::hasTable('user_directorate_relation')) {
            Schema::table('user_directorate_relation', function (Blueprint $table): void {
                $table->dropUnique('user_directorate_relation_user_id_directorate_id_unique');
                $table->unique('user_id', 'user_directorate_relation_user_id_unique');
            });
        }

        if (Schema::hasTable('user_lab_relation')) {
            Schema::table('user_lab_relation', function (Blueprint $table): void {
                $table->dropUnique('user_lab_relation_user_id_lab_id_unique');
                $table->unique('user_id', 'user_lab_relation_user_id_unique');
            });
        }
    }
};
