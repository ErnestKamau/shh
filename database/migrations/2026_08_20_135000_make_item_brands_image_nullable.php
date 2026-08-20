<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('item_brands') || ! Schema::hasColumn('item_brands', 'image')) {
            return;
        }

        DB::statement('ALTER TABLE item_brands ALTER COLUMN image DROP NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('item_brands') || ! Schema::hasColumn('item_brands', 'image')) {
            return;
        }

        DB::statement("UPDATE item_brands SET image = '' WHERE image IS NULL");
        DB::statement('ALTER TABLE item_brands ALTER COLUMN image SET NOT NULL');
    }
};
