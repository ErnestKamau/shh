<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow pdf/docx extensions for customer contract attachments.
     * Legacy enum only allowed screenshot/document/other.
     */
    public function up(): void
    {
        if (! Schema::hasTable('crm_customer_attachments') || ! Schema::hasColumn('crm_customer_attachments', 'file_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->expandPostgresCheck();
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE crm_customer_attachments MODIFY file_type ENUM('screenshot', 'document', 'other', 'pdf', 'docx') NOT NULL DEFAULT 'document'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_customer_attachments') || ! Schema::hasColumn('crm_customer_attachments', 'file_type')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE crm_customer_attachments DROP CONSTRAINT IF EXISTS crm_customer_attachments_file_type_check');
            DB::statement("
                ALTER TABLE crm_customer_attachments
                ADD CONSTRAINT crm_customer_attachments_file_type_check
                CHECK (file_type::text = ANY (ARRAY['screenshot'::character varying, 'document'::character varying, 'other'::character varying]::text[]))
            ");
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE crm_customer_attachments MODIFY file_type ENUM('screenshot', 'document', 'other') NOT NULL DEFAULT 'document'");
        }
    }

    protected function expandPostgresCheck(): void
    {
        $constraints = DB::select("
            SELECT c.conname AS name
            FROM pg_constraint c
            JOIN pg_class t ON c.conrelid = t.oid
            JOIN pg_namespace n ON t.relnamespace = n.oid
            WHERE n.nspname = current_schema()
              AND t.relname = 'crm_customer_attachments'
              AND c.contype = 'c'
              AND (
                    c.conname = 'crm_customer_attachments_file_type_check'
                    OR pg_get_constraintdef(c.oid) ILIKE '%file_type%'
              )
        ");

        foreach ($constraints as $constraint) {
            $name = str_replace('"', '""', $constraint->name);
            DB::statement("ALTER TABLE crm_customer_attachments DROP CONSTRAINT IF EXISTS \"{$name}\"");
        }

        DB::statement("
            ALTER TABLE crm_customer_attachments
            ADD CONSTRAINT crm_customer_attachments_file_type_check
            CHECK (file_type::text = ANY (ARRAY[
                'screenshot'::character varying,
                'document'::character varying,
                'other'::character varying,
                'pdf'::character varying,
                'docx'::character varying
            ]::text[]))
        ");
    }
};
