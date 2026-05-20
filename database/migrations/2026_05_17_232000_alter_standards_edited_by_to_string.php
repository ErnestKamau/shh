<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Change standards.edited_by from integer to varchar(255)
        if (Schema::hasTable('standards')) {
            DB::statement("ALTER TABLE standards ALTER COLUMN edited_by TYPE VARCHAR(255) USING edited_by::text");
        }

        // 2. Change standard_values.edited_by from integer to varchar(255)
        if (Schema::hasTable('standard_values')) {
            DB::statement("ALTER TABLE standard_values ALTER COLUMN edited_by TYPE VARCHAR(255) USING edited_by::text");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('standards')) {
            DB::statement(
                "ALTER TABLE standards
                 ALTER COLUMN edited_by TYPE integer
                 USING (
                    CASE
                        WHEN edited_by IS NULL THEN NULL
                        WHEN edited_by ~ '^[0-9]+$' THEN edited_by::integer
                        ELSE NULL
                    END
                 )"
            );
        }

        if (Schema::hasTable('standard_values')) {
            DB::statement(
                "ALTER TABLE standard_values
                 ALTER COLUMN edited_by TYPE integer
                 USING (
                    CASE
                        WHEN edited_by IS NULL THEN NULL
                        WHEN edited_by ~ '^[0-9]+$' THEN edited_by::integer
                        ELSE NULL
                    END
                 )"
            );
        }
    }
};
