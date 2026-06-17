<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS quotation_header_view');

        DB::statement(<<<'SQL'
            CREATE VIEW quotation_header_view AS
            SELECT
                qh.id AS id,
                qh.created_at AS created_at,
                qh.updated_at AS updated_at,
                qh.quote_number AS quote_number,
                qh.crm_customer_id AS crm_customer_id,
                qh.crm_customer_contact_id AS crm_customer_contact_id,
                qh.quote_date AS quote_date,
                qh.expiring_date AS expiring_date,
                qh.prepared_by_id AS prepared_by_id,
                qh.email_to_customer AS email_to_customer,
                qh.is_draft AS is_draft,
                qh.total_amount AS total_amount,
                qh.is_complete AS is_complete,
                qh.sub_total AS sub_total,
                qh.tax AS tax,
                qh.is_print AS is_print,
                qh.upload_url AS upload_url,
                qh.status AS status,
                qh.price AS price,
                qh.service_delivery AS service_delivery,
                qh.payments AS payments,
                qh.quote_specification AS quote_specification,
                qh.additional_info AS additional_info,
                qh.payment_info AS payment_info,
                qh.payment AS payment,
                qh.approved_by AS approved_by,
                qh.quotation_type AS quotation_type,
                qh.currency_id AS currency_id,
                qh.is_approved AS is_approved,
                cc.name AS customer,
                ccc.first_name AS contact_first,
                ccc.middle_name AS contact_middle,
                ccc.last_name AS contact_last,
                u.name AS prepared_by_name
            FROM quotation_headers qh
            JOIN crm_customers cc
                ON qh.crm_customer_id = cc.id
            JOIN crm_customer_contacts ccc
                ON qh.crm_customer_contact_id = ccc.id
            JOIN users u
                ON qh.prepared_by_id::text = u.id::text
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS quotation_header_view');
    }
};
