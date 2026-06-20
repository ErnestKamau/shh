<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_headers')) {
            return;
        }

        $newerViewMigrationRan = DB::table('migrations')
            ->where('migration', '2026_06_16_123613_create_quotation_header_view')
            ->exists();

        if ($newerViewMigrationRan) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS quotation_header_view');

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW quotation_header_view AS
SELECT
    qh.id,
    qh.created_at,
    qh.updated_at,
    qh.quote_number,
    qh.crm_customer_id,
    qh.crm_customer_contact_id,
    qh.quote_date,
    qh.expiring_date,
    qh.prepared_by_id,
    qh.email_to_customer,
    qh.pricelist_id,
    qh.is_draft,
    qh.total_amount,
    qh.is_complete,
    qh.sub_total,
    qh.tax,
    qh.is_print,
    qh.upload_url,
    qh.status,
    qh.price,
    qh.service_delivery,
    qh.payments,
    qh.quote_specification,
    qh.additional_info,
    qh.payment_info,
    qh.payment,
    qh.approved_by,
    qh.quotation_type,
    qh.currency_id,
    qh.is_approved,
    cc.name AS customer,
    ccc.first_name AS contact_first,
    ccc.middle_name AS contact_middle,
    ccc.last_name AS contact_last,
    u.name AS prepared_by_name,
    pl.code AS pricelist
FROM quotation_headers qh
LEFT JOIN crm_customers cc ON qh.crm_customer_id = cc.id
LEFT JOIN crm_customer_contacts ccc ON qh.crm_customer_contact_id = ccc.id
LEFT JOIN users u ON qh.prepared_by_id = u.id
LEFT JOIN pricelists pl ON qh.pricelist_id = pl.id
SQL
        );
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS quotation_header_view');
    }
};
