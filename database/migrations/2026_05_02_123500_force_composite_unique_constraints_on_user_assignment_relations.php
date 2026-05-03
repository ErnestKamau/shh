<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE user_zone_relation DROP CONSTRAINT IF EXISTS user_zone_relation_user_id_unique');
        DB::statement('ALTER TABLE user_zone_relation DROP CONSTRAINT IF EXISTS user_zone_relation_user_id_zone_id_unique');
        DB::statement('ALTER TABLE user_zone_relation ADD CONSTRAINT user_zone_relation_user_id_zone_id_unique UNIQUE (user_id, zone_id)');

        DB::statement('ALTER TABLE user_directorate_relation DROP CONSTRAINT IF EXISTS user_directorate_relation_user_id_unique');
        DB::statement('ALTER TABLE user_directorate_relation DROP CONSTRAINT IF EXISTS user_directorate_relation_user_id_directorate_id_unique');
        DB::statement('ALTER TABLE user_directorate_relation ADD CONSTRAINT user_directorate_relation_user_id_directorate_id_unique UNIQUE (user_id, directorate_id)');

        DB::statement('ALTER TABLE user_lab_relation DROP CONSTRAINT IF EXISTS user_lab_relation_user_id_unique');
        DB::statement('ALTER TABLE user_lab_relation DROP CONSTRAINT IF EXISTS user_lab_relation_user_id_lab_id_unique');
        DB::statement('ALTER TABLE user_lab_relation ADD CONSTRAINT user_lab_relation_user_id_lab_id_unique UNIQUE (user_id, lab_id)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE user_zone_relation DROP CONSTRAINT IF EXISTS user_zone_relation_user_id_zone_id_unique');
        DB::statement('ALTER TABLE user_zone_relation ADD CONSTRAINT user_zone_relation_user_id_unique UNIQUE (user_id)');

        DB::statement('ALTER TABLE user_directorate_relation DROP CONSTRAINT IF EXISTS user_directorate_relation_user_id_directorate_id_unique');
        DB::statement('ALTER TABLE user_directorate_relation ADD CONSTRAINT user_directorate_relation_user_id_unique UNIQUE (user_id)');

        DB::statement('ALTER TABLE user_lab_relation DROP CONSTRAINT IF EXISTS user_lab_relation_user_id_lab_id_unique');
        DB::statement('ALTER TABLE user_lab_relation ADD CONSTRAINT user_lab_relation_user_id_unique UNIQUE (user_id)');
    }
};
