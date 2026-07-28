<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE stock_transfers
            ALTER COLUMN created_by TYPE uuid
            USING CASE
                WHEN created_by IS NULL OR BTRIM(created_by::text) = '' THEN NULL
                WHEN created_by::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                    THEN created_by::text::uuid
                ELSE NULL
            END
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE stock_transfers ALTER COLUMN created_by TYPE character varying(255) USING created_by::text');
    }
};
