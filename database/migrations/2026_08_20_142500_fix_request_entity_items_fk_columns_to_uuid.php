<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * request_entities / inventory stores use uuid ids, but request_entity_items
     * still had integer FK columns for request_id, store_id and slot_id.
     */
    public function up(): void
    {
        // view_request_entities joins request_entity_items.request_id; drop it before ALTER.
        DB::statement('DROP VIEW IF EXISTS view_request_entities');

        $this->convertIntegerFkToUuid('request_entity_items', 'request_id');
        $this->convertIntegerFkToUuid('request_entity_items', 'store_id');
        $this->convertIntegerFkToUuid('request_entity_items', 'slot_id');
        $this->convertIntegerFkToUuid('request_entity_items', 'issued_by');
        $this->convertIntegerFkToUuid('request_entity_items', 'request_entity_item_ammended_id');

        $this->createViewRequestEntities();
    }

    public function down(): void
    {
        // Not reversed — legacy integer ids cannot be restored safely.
    }

    private function convertIntegerFkToUuid(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $type = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );

        if (! $type || $type->data_type === 'uuid') {
            return;
        }

        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
        DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");

        DB::statement("
            ALTER TABLE {$table}
            ALTER COLUMN {$column} TYPE uuid
            USING CASE
                WHEN {$column}::text ~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$'
                    THEN {$column}::text::uuid
                ELSE NULL
            END
        ");
    }

    private function createViewRequestEntities(): void
    {
        if (! Schema::hasTable('request_entities') || ! Schema::hasColumn('request_entities', 'is_supplement')) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS view_request_entities');

        DB::statement(<<<'SQL'
            CREATE VIEW view_request_entities AS
            SELECT
                re.id,
                re.priority,
                re.currency,
                re.request_code,
                re.status,
                re.parent_request,
                re.due_date,
                re.parent_request_id,
                re.request_type,
                re.approval_count,
                re.required_approvals,
                re.created_by,
                re.description,
                re.nature_of_purchase,
                re.created_at,
                re.updated_at,
                re.net_value,
                re.is_lab_kit,
                re.quote_net_value,
                re.submission_deadline,
                re.supplier_id,
                COALESCE(re.delete, 0) AS delete,
                re.client_unit_id,
                re.request_initiator,
                re.inventory_location_id,
                re.parent_material_requisition,
                re.approval_status,
                re.ammendment,
                re.in_ammendment,
                re.quotes_reminder_sent,
                re.gate_pass,
                re.time_out,
                re.vehicle_no,
                re.issue_to,
                re.usage,
                re.note_bearer,
                re.destination,
                re.bank_notified,
                re.supplier_bank_notification,
                re.cost_center,
                re.downloadable_link,
                re.catalog_number,
                re.kit_total_price,
                COALESCE(re.is_supplement, false) AS is_supplement,
                COALESCE(u.name, '') AS creator_name,
                COALESCE(d.name, '') AS departmental_name,
                u.department_id AS department_id,
                COALESCE(s.name, '') AS supplier_name,
                parent.request_code AS parent_request_code,
                COALESCE((
                    SELECT string_agg(DISTINCT isc.name, ', ' ORDER BY isc.name)
                    FROM request_entity_items rei
                    INNER JOIN inventory_sub_categories isc
                        ON isc.id::text = rei.inventory_sub_category_id::text
                    WHERE rei.request_id::text = re.id::text
                      AND COALESCE(rei.action, 'normal') = 'normal'
                ), '') AS item_names
            FROM request_entities re
            LEFT JOIN users u
                ON u.id::text = re.created_by::text
            LEFT JOIN inventory_departments d
                ON d.id::text = u.department_id::text
            LEFT JOIN suppliers s
                ON s.id::text = re.supplier_id::text
            LEFT JOIN request_entities parent
                ON parent.id::text = re.parent_request_id::text
        SQL);
    }
};
