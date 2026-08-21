<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('inventory_categories')) {
            return;
        }

        if (Schema::hasColumn('inventory_categories', 'description')) {
            DB::statement('ALTER TABLE inventory_categories ALTER COLUMN description DROP NOT NULL');
        }

        if (Schema::hasColumn('inventory_categories', 'image')) {
            DB::statement('ALTER TABLE inventory_categories ALTER COLUMN image DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('inventory_categories')) {
            return;
        }

        if (Schema::hasColumn('inventory_categories', 'description')) {
            DB::statement("UPDATE inventory_categories SET description = '' WHERE description IS NULL");
            DB::statement('ALTER TABLE inventory_categories ALTER COLUMN description SET NOT NULL');
        }

        if (Schema::hasColumn('inventory_categories', 'image')) {
            DB::statement("UPDATE inventory_categories SET image = '' WHERE image IS NULL");
            DB::statement('ALTER TABLE inventory_categories ALTER COLUMN image SET NOT NULL');
        }
    }
};
