<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE supplier_categories ALTER COLUMN inventory_item_brand_id DROP DEFAULT');
        DB::statement('ALTER TABLE supplier_categories ALTER COLUMN inventory_item_brand_id TYPE uuid USING NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE supplier_categories ALTER COLUMN inventory_item_brand_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE supplier_categories ALTER COLUMN inventory_item_brand_id SET DEFAULT 0');
    }
};
