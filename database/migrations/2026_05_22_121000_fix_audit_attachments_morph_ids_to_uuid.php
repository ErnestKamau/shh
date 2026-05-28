<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Audit module parents (Audit, NonConformance, etc.) use UUID primary keys;
     * attachable_id must not be bigint.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audit_attachments')) {
            return;
        }

        if (! Schema::hasColumn('audit_attachments', 'attachable_id')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audit_attachments ALTER COLUMN attachable_id TYPE VARCHAR(36) USING attachable_id::text');

            if (Schema::hasColumn('audit_attachments', 'uploaded_by')) {
                DB::statement('ALTER TABLE audit_attachments ALTER COLUMN uploaded_by DROP NOT NULL');
                DB::statement('ALTER TABLE audit_attachments ALTER COLUMN uploaded_by TYPE VARCHAR(36) USING uploaded_by::text');
            }

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE audit_attachments MODIFY attachable_id VARCHAR(36) NOT NULL');

            if (Schema::hasColumn('audit_attachments', 'uploaded_by')) {
                DB::statement('ALTER TABLE audit_attachments MODIFY uploaded_by VARCHAR(36) NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('audit_attachments')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE audit_attachments ALTER COLUMN attachable_id TYPE bigint USING CASE WHEN attachable_id ~ '^[0-9]+$' THEN attachable_id::bigint ELSE 0 END");

            if (Schema::hasColumn('audit_attachments', 'uploaded_by')) {
                DB::statement("ALTER TABLE audit_attachments ALTER COLUMN uploaded_by TYPE bigint USING CASE WHEN uploaded_by ~ '^[0-9]+$' THEN uploaded_by::bigint ELSE 0 END");
                DB::statement('ALTER TABLE audit_attachments ALTER COLUMN uploaded_by SET NOT NULL');
            }

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE audit_attachments MODIFY attachable_id BIGINT UNSIGNED NOT NULL');

            if (Schema::hasColumn('audit_attachments', 'uploaded_by')) {
                DB::statement('ALTER TABLE audit_attachments MODIFY uploaded_by BIGINT UNSIGNED NOT NULL');
            }
        }
    }
};
