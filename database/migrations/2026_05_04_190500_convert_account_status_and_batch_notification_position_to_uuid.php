<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE crm_customers
            ALTER COLUMN account_status TYPE uuid
            USING (
                CASE
                    WHEN account_status IS NULL THEN NULL
                    WHEN account_status::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$' THEN account_status::text::uuid
                    ELSE NULL
                END
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE batch_notifications
            ALTER COLUMN position_id TYPE uuid
            USING (
                CASE
                    WHEN position_id IS NULL THEN NULL
                    WHEN position_id::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$' THEN position_id::text::uuid
                    ELSE NULL
                END
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE crm_customers
            ALTER COLUMN account_status TYPE integer
            USING (
                CASE
                    WHEN account_status IS NULL THEN NULL
                    WHEN account_status::text ~ '^[0-9]+$' THEN account_status::text::integer
                    ELSE NULL
                END
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE batch_notifications
            ALTER COLUMN position_id TYPE integer
            USING (
                CASE
                    WHEN position_id IS NULL THEN NULL
                    WHEN position_id::text ~ '^[0-9]+$' THEN position_id::text::integer
                    ELSE NULL
                END
            )
        SQL);
    }
};
