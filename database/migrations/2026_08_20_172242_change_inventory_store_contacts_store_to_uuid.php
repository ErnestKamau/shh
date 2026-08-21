<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('inventory_store_contacts')) {
            return;
        }

        if (! Schema::hasColumn('inventory_store_contacts', 'store')) {
            return;
        }

        $dataType = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'inventory_store_contacts')
            ->where('column_name', 'store')
            ->value('data_type');

        if ($dataType === 'uuid') {
            return;
        }

        DB::statement('ALTER TABLE inventory_store_contacts ALTER COLUMN store DROP DEFAULT');
        DB::statement('ALTER TABLE inventory_store_contacts ALTER COLUMN store DROP NOT NULL');
        DB::statement("
            ALTER TABLE inventory_store_contacts
            ALTER COLUMN store TYPE uuid
            USING CASE
                WHEN store::text IS NULL THEN NULL
                WHEN store::text IN ('0', '') THEN NULL
                WHEN store::text ~ '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                    THEN store::text::uuid
                ELSE NULL
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql' || ! Schema::hasTable('inventory_store_contacts')) {
            return;
        }

        if (! Schema::hasColumn('inventory_store_contacts', 'store')) {
            return;
        }

        DB::statement('ALTER TABLE inventory_store_contacts ALTER COLUMN store DROP DEFAULT');
        DB::statement('ALTER TABLE inventory_store_contacts ALTER COLUMN store DROP NOT NULL');
        DB::statement("
            ALTER TABLE inventory_store_contacts
            ALTER COLUMN store TYPE integer
            USING CASE
                WHEN store IS NULL THEN NULL
                WHEN store::text ~ '^[0-9]+$' THEN store::text::integer
                ELSE NULL
            END
        ");
    }
};
