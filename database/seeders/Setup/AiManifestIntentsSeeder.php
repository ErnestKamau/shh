<?php

namespace Database\Seeders\Setup;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiManifestIntentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $connection = 'pgsql_ai';

        $this->command?->info('====================================================');
        $this->command?->info('STARTING SEEDING: AI Manifest Intents & Patterns');
        $this->command?->info('====================================================');

        DB::connection($connection)->transaction(function () use ($connection) {
            // 1. Clear existing manifest records
            DB::connection($connection)->table('manifest_intent_patterns')->delete();
            DB::connection($connection)->table('manifest_intents')->delete();
            $this->command?->info('Cleared existing AI manifest and pattern records.');

            // 2. Define intents
            $intents = [
            [
                'id' => 'sample_count_total',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true',
                'description' => 'Total active sample batches logged in the operational database',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_absolute_all_time',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers',
                'description' => 'Absolute total sample batches ever created since system launch, including deleted, archived, and cancelled records',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'individual_sample_count',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_details sd INNER JOIN public.sample_headers sh ON sh.id = sd.sample_header_id WHERE sh.isactive = true',
                'description' => 'Total active individual sample items',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_pending_review',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT status, COUNT(*) as cnt FROM public.sample_headers WHERE isactive = true AND status IN (\'Technical Verification\', \'Sample Receipt & Preparation\', \'Sample Request & Submission\') GROUP BY status ORDER BY cnt DESC',
                'description' => 'Sample batches awaiting review, verification, or preparation',
                'output_format' => 'table',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_request_review',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND status = \'Samples Request Review\'',
                'description' => 'Sample batches currently in request review',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_approved',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND status = \'Sample Approval\'',
                'description' => 'Sample batches that are approved',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_verified',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND status = \'Sample Verification\'',
                'description' => 'Sample batches that are verified',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_in_lab',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers sh WHERE sh.isactive = true AND sh.status = \'Samples In Lab\'',
                'description' => 'Sample batches currently in the lab',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'batch_count_in_lab',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers sh WHERE sh.isactive = true AND sh.status IN (\'Analysis in Progress\', \'Technical Verification\', \'Sample Receipt & Preparation\')',
                'description' => 'Sample batches currently in the lab',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'samples_by_status',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT sh.status, COUNT(sd.id) as cnt FROM public.sample_details sd INNER JOIN public.sample_headers sh ON sh.id = sd.sample_header_id WHERE sh.isactive = true GROUP BY sh.status ORDER BY cnt DESC',
                'description' => 'Distribution of individual samples by batch status',
                'output_format' => 'table',
                'ttl_seconds' => 15,
            ],
            [
                'id' => 'batch_count_total',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true',
                'description' => 'Total active sample batches',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_today',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND created_at::date = CURRENT_DATE',
                'description' => 'Sample batches received today',
                'output_format' => 'count',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'sample_count_this_week',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND created_at >= DATE_TRUNC(\'week\', CURRENT_DATE)',
                'description' => 'Sample batches received this week',
                'output_format' => 'count',
                'ttl_seconds' => 30,
            ],
            [
                'id' => 'sample_count_this_month',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND created_at >= DATE_TRUNC(\'month\', CURRENT_DATE)',
                'description' => 'Sample batches received this month',
                'output_format' => 'count',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'samples_rejected',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND LOWER(COALESCE(status, \'\')) IN (\'rejected\', \'cancelled\', \'sample rejected\')',
                'description' => 'Rejected or cancelled sample batches',
                'output_format' => 'count',
                'ttl_seconds' => 30,
            ],
            [
                'id' => 'latest_received_batches',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT batch_code, status, created_at::date as received_date FROM public.sample_headers WHERE isactive = true ORDER BY created_at DESC LIMIT 10',
                'description' => 'Latest received sample batches',
                'output_format' => 'table',
                'ttl_seconds' => 10,
            ],
            [
                'id' => 'daily_ingestion_trend',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT created_at::date as day, COUNT(*) as batches FROM public.sample_headers WHERE created_at >= (NOW() - INTERVAL \'14 days\') AND isactive = true GROUP BY day ORDER BY day DESC',
                'description' => 'Batch registration volume per day for the last 14 days',
                'output_format' => 'table',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'sample_type_distribution',
                'domain' => 'samples',
                'group_type' => 'A',
                'sql_query' => 'SELECT COALESCE(st.name, \'Unspecified\') as sample_type, COUNT(*) as count FROM public.sample_headers sh LEFT JOIN public.sample_types st ON sh.sample_type_id = st.id WHERE sh.isactive = true GROUP BY COALESCE(st.name, \'Unspecified\') ORDER BY count DESC',
                'description' => 'Sample volume by sample type name',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'inventory_low_stock',
                'domain' => 'inventory',
                'group_type' => 'A',
                'sql_query' => 'SELECT batch_code, status, stock_in, stock_out, (stock_in - stock_out) as balance FROM public.inventory_items WHERE (stock_in - stock_out) <= 0 ORDER BY updated_at DESC LIMIT 10',
                'description' => 'Inventory items with zero or negative calculated balance',
                'output_format' => 'table',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'inventory_order_status',
                'domain' => 'inventory',
                'group_type' => 'A',
                'sql_query' => 'SELECT po_number, status as fulfillment_status, supplier_id::text as supplier, created_at FROM public.inventory_items WHERE po_number IS NOT NULL ORDER BY created_at DESC LIMIT 10',
                'description' => 'Recent inventory purchase order references',
                'output_format' => 'table',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'supplier_order_performance',
                'domain' => 'inventory',
                'group_type' => 'A',
                'sql_query' => 'SELECT supplier_id::text as supplier, COUNT(*) as total_items, SUM(CASE WHEN status = \'in_inventory\' THEN 1 ELSE 0 END) as in_inventory FROM public.inventory_items WHERE supplier_id IS NOT NULL AND created_at >= (NOW() - INTERVAL \'90 days\') GROUP BY supplier_id ORDER BY total_items DESC LIMIT 10',
                'description' => 'Inventory receipts grouped by supplier id',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'inventory_stock_by_category',
                'domain' => 'inventory',
                'group_type' => 'A',
                'sql_query' => 'SELECT inventory_category_id::text as category, COUNT(*) as item_count, SUM(stock_in - stock_out) as balance FROM public.inventory_items GROUP BY inventory_category_id ORDER BY balance ASC LIMIT 20',
                'description' => 'Inventory balance by category id',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'inventory_expiring_soon',
                'domain' => 'inventory',
                'group_type' => 'A',
                'sql_query' => 'SELECT batch_code, expiry, (stock_in - stock_out) as balance FROM public.inventory_items WHERE expiry BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL \'30 days\' ORDER BY expiry ASC LIMIT 15',
                'description' => 'Inventory batches expiring in the next 30 days',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'equipment_utilization',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT name, equipment_number, status, active FROM public.equipment ORDER BY updated_at DESC LIMIT 10',
                'description' => 'Recent equipment records from the operational database',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'equipment_maintenance_schedule',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT name, equipment_number, (date_purchased + (maintainance_days || \' days\')::interval)::date as next_maintenance_due, assigned_department FROM public.equipment WHERE active = true ORDER BY next_maintenance_due ASC LIMIT 10',
                'description' => 'Upcoming equipment maintenance dates calculated from purchase date and maintenance interval',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'equipment_downtime_summary',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT name, equipment_number, status, condition FROM public.equipment WHERE active = false OR LOWER(COALESCE(status, \'\')) IN (\'down\', \'inactive\', \'broken\', \'out of service\') ORDER BY updated_at DESC LIMIT 10',
                'description' => 'Equipment records that appear inactive or unavailable',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'equipment_count_active',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.equipment WHERE active = true',
                'description' => 'Active equipment count',
                'output_format' => 'count',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'equipment_count_total',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.equipment',
                'description' => 'Total equipment count including inactive',
                'output_format' => 'count',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'equipment_count_inactive',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.equipment WHERE active = false',
                'description' => 'Inactive or decommissioned equipment count',
                'output_format' => 'count',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'equipment_maintenance_health',
                'domain' => 'equipment',
                'group_type' => 'B',
                'sql_query' => 'SELECT status, COUNT(*) as cnt FROM public.equipment GROUP BY status ORDER BY cnt DESC',
                'description' => 'Equipment status distribution',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'equipment_verification_status',
                'domain' => 'equipment',
                'group_type' => 'A',
                'sql_query' => 'SELECT name, equipment_number, verificaction_days, verification_notification_in_days, updated_at::date as last_updated FROM public.equipment WHERE active = true ORDER BY updated_at ASC LIMIT 15',
                'description' => 'Equipment verification configuration and last update date',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'qc_pass_rate',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as total, SUM(CASE WHEN LOWER(COALESCE(status_code, \'\')) IN (\'pass\', \'passed\', \'approved\') THEN 1 ELSE 0 END) as passing FROM public.qc_results',
                'description' => 'QC result totals and passing count',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'qc_drifting_analytes',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT analyte_id::text as analyte, robust_cv_percentage FROM public.qc_processed_result WHERE robust_cv_percentage > 15 ORDER BY robust_cv_percentage DESC LIMIT 10',
                'description' => 'QC analytes with robust CV above warning threshold',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'capa_pending',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.corrective_actions WHERE deleted_at IS NULL AND LOWER(COALESCE(status_name, \'\')) NOT IN (\'closed\', \'completed\', \'cancelled\')',
                'description' => 'Open corrective actions',
                'output_format' => 'count',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'samples_pending_qc',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.sample_headers WHERE isactive = true AND is_qc_batch = true AND LOWER(COALESCE(status, \'\')) NOT IN (\'completed\', \'approved\')',
                'description' => 'QC sample batches not completed or approved',
                'output_format' => 'count',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'complaints_trend',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT DATE_TRUNC(\'month\', created_at)::date as month, COUNT(*) as complaints FROM public.complaints WHERE created_at >= (NOW() - INTERVAL \'6 months\') GROUP BY month ORDER BY month DESC',
                'description' => 'Complaint volume by month',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'audit_findings_open',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT \'audit_findings\' as category, 0 as open_count',
                'description' => 'Open audit findings placeholder until direct operational audit table mapping is confirmed',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'qc_stability_report',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT analyte_name, robust_cv_pct, pass_rate_pct, total_tests FROM public.v_qc_stability_metrics ORDER BY robust_cv_pct DESC LIMIT 10',
                'description' => 'Top 10 analytes by variability (CV%) and their pass rates',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'qc_out_of_control_events',
                'domain' => 'quality',
                'group_type' => 'A',
                'sql_query' => 'SELECT analyte_name, test_date, avg_value, center_line, control_status FROM public.v_qc_drift_trends WHERE control_status = \'OUT_OF_CONTROL\' ORDER BY test_date DESC LIMIT 15',
                'description' => 'Recent QC results that triggered Out-of-Control alerts',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'tat_overall_average',
                'domain' => 'tat',
                'group_type' => 'A',
                'sql_query' => 'SELECT ROUND(AVG(ABS(tat_overdue_days) * 24.0), 1) as avg_tat_hours FROM public.tat_captured WHERE created_at >= (NOW() - INTERVAL \'30 days\') AND is_complete = true',
                'description' => 'Average TAT offset magnitude in hours for completed records in the last 30 days (ABS used because column is signed: negative = early, positive = late)',
                'output_format' => 'count',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'tat_overdue_batches',
                'domain' => 'tat',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(DISTINCT sample_header_id) as overdue_batches FROM public.tat_captured WHERE tat_overdue_days > 0 AND is_complete = false AND created_at >= (NOW() - INTERVAL \'30 days\')',
                'description' => 'Batches with overdue unfinished TAT records in the last 30 days',
                'output_format' => 'count',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'tat_by_analyte',
                'domain' => 'tat',
                'group_type' => 'A',
                'sql_query' => 'SELECT analyte_id::text as analyte, ROUND(AVG(ABS(tat_overdue_days) * 24.0), 1) as avg_hours, COUNT(*) as test_count FROM public.tat_captured WHERE created_at >= (NOW() - INTERVAL \'30 days\') GROUP BY analyte_id ORDER BY avg_hours DESC LIMIT 10',
                'description' => 'Average TAT offset magnitude in hours by analyte id (sorted by highest deviation regardless of direction)',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'tat_sla_compliance',
                'domain' => 'tat',
                'group_type' => 'A',
                'sql_query' => 'SELECT ROUND(100.0 * SUM(CASE WHEN tat_overdue_days <= 0 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 1) as sla_pct FROM public.tat_captured WHERE created_at >= (NOW() - INTERVAL \'30 days\') AND is_complete = true',
                'description' => 'Completed TAT records within SLA over the last 30 days',
                'output_format' => 'percentage',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'lab_tat_stage_summary',
                'domain' => 'tat',
                'group_type' => 'A',
                'sql_query' => 'SELECT workflow_stage, total_batches, overdue_batches, due_today_batches, ROUND(avg_days_to_target::numeric, 1) as avg_days_to_target FROM public.v_lab_tat_stage_summary ORDER BY total_batches DESC',
                'description' => 'Summary of batches per workflow stage with overdue/due-today counts',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'lab_tat_aging_report',
                'domain' => 'tat',
                'group_type' => 'A',
                'sql_query' => 'SELECT aging_bucket, batch_count FROM public.v_lab_tat_aging_buckets',
                'description' => 'Distribution of active batches by their aging/overdue status',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'complaint_count_open',
                'domain' => 'support',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.complaints WHERE is_closed = false',
                'description' => 'Open complaints',
                'output_format' => 'count',
                'ttl_seconds' => 60,
            ],
            [
                'id' => 'ticket_backlog_priority',
                'domain' => 'support',
                'group_type' => 'A',
                'sql_query' => 'SELECT priority, COUNT(*) as cnt FROM public.complaints WHERE is_closed = false GROUP BY priority ORDER BY cnt DESC',
                'description' => 'Open complaints by priority',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'ticket_resolution_time',
                'domain' => 'support',
                'group_type' => 'A',
                'sql_query' => 'SELECT priority, ROUND(AVG(EXTRACT(EPOCH FROM (resolved_time - created_at)) / 3600), 1) as avg_resolution_hours FROM public.complaints WHERE resolved_time IS NOT NULL AND created_at >= (NOW() - INTERVAL \'90 days\') GROUP BY priority ORDER BY avg_resolution_hours DESC',
                'description' => 'Average complaint resolution hours by priority',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'tickets_by_department',
                'domain' => 'support',
                'group_type' => 'A',
                'sql_query' => 'SELECT current_department, COUNT(*) as total, SUM(CASE WHEN is_closed = false THEN 1 ELSE 0 END) as open_count FROM public.complaints WHERE created_at >= (NOW() - INTERVAL \'90 days\') GROUP BY current_department ORDER BY open_count DESC LIMIT 10',
                'description' => 'Complaint workload by current department',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'sla_compliance',
                'domain' => 'support',
                'group_type' => 'B',
                'sql_query' => 'SELECT ROUND(100.0 * SUM(CASE WHEN resolution_sla_status = \'Achieved\' THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 1) as sla_pct FROM public.complaints WHERE created_at >= (NOW() - INTERVAL \'90 days\')',
                'description' => 'Complaint resolution SLA compliance',
                'output_format' => 'percentage',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'analyst_count_active',
                'domain' => 'personnel',
                'group_type' => 'A',
                'sql_query' => 'SELECT COUNT(*) as n FROM public.users WHERE active = 1 AND COALESCE(is_client, false) = false AND COALESCE(is_support_staff, false) = false AND COALESCE(is_tablet, false) = false AND (lab_id IS NOT NULL OR lab_section_id IS NOT NULL OR COALESCE(analyst_is_gazzetted, false) = true OR LOWER(COALESCE(designation, \'\')) LIKE \'%analyst%\')',
                'description' => 'Active analyst users in the system',
                'output_format' => 'count',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'analyst_verifications',
                'domain' => 'personnel',
                'group_type' => 'B',
                'sql_query' => 'SELECT u.name, COUNT(sh.id) as batches_verified FROM public.users u JOIN public.sample_headers sh ON u.id = sh.verify_user_id WHERE sh.created_at >= (NOW() - INTERVAL \'30 days\') AND u.active = 1 GROUP BY u.name ORDER BY batches_verified DESC LIMIT 15',
                'description' => 'Batches verified by analyst in the last 30 days',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'analyst_approvals',
                'domain' => 'personnel',
                'group_type' => 'A',
                'sql_query' => 'SELECT u.name, COUNT(sh.id) as batches_approved FROM public.users u JOIN public.sample_headers sh ON u.id = sh.approve_user_id WHERE sh.created_at >= (NOW() - INTERVAL \'30 days\') AND u.active = 1 GROUP BY u.name ORDER BY batches_approved DESC LIMIT 15',
                'description' => 'Batches approved by analyst in the last 30 days',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'analyst_workload_today',
                'domain' => 'personnel',
                'group_type' => 'A',
                'sql_query' => 'SELECT u.name as analyst, COUNT(sh.id) as batches_touched FROM public.users u JOIN public.sample_headers sh ON u.id IN (sh.verify_user_id, sh.approve_user_id) WHERE sh.updated_at::date = CURRENT_DATE AND u.active = 1 GROUP BY u.name ORDER BY batches_touched DESC LIMIT 15',
                'description' => 'Analyst sample activity today',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ],
            [
                'id' => 'top_clients_by_volume',
                'domain' => 'crm',
                'group_type' => 'A',
                'sql_query' => 'SELECT c.name as client, COUNT(sh.id) as sample_batches FROM public.crm_customers c JOIN public.sample_headers sh ON c.id = sh.crm_customer_id WHERE sh.created_at >= (NOW() - INTERVAL \'90 days\') AND sh.isactive = true GROUP BY c.name ORDER BY sample_batches DESC LIMIT 10',
                'description' => 'Top customers by sample batch volume in the last 90 days',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'inactive_clients',
                'domain' => 'crm',
                'group_type' => 'A',
                'sql_query' => 'SELECT c.name, MAX(sh.created_at)::date as last_submission FROM public.crm_customers c LEFT JOIN public.sample_headers sh ON c.id = sh.crm_customer_id GROUP BY c.name HAVING MAX(sh.created_at) < (NOW() - INTERVAL \'60 days\') OR MAX(sh.created_at) IS NULL ORDER BY last_submission ASC NULLS FIRST LIMIT 25',
                'description' => 'Customers with no sample submissions in the last 60 days',
                'output_format' => 'table',
                'ttl_seconds' => 300,
            ],
            [
                'id' => 'client_submission_today',
                'domain' => 'crm',
                'group_type' => 'A',
                'sql_query' => 'SELECT c.name as client, COUNT(sh.id) as batches_today FROM public.crm_customers c JOIN public.sample_headers sh ON c.id = sh.crm_customer_id WHERE sh.created_at::date = CURRENT_DATE AND sh.isactive = true GROUP BY c.name ORDER BY batches_today DESC LIMIT 15',
                'description' => 'Customer sample submissions today',
                'output_format' => 'table',
                'ttl_seconds' => 120,
            ]
            ];

            // 3. Define patterns
            $patterns = [
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'samples in the lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'sample count in lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'how many samples in lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'how many samples are in the lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'how many samples are in lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'samples currently in lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'individual samples in lab',
            ],
            [
                'intent_id' => 'sample_count_in_lab',
                'pattern' => 'samples in lab',
            ],
            [
                'intent_id' => 'batch_count_in_lab',
                'pattern' => 'batches in the lab',
            ],
            [
                'intent_id' => 'batch_count_in_lab',
                'pattern' => 'batch count in lab',
            ],
            [
                'intent_id' => 'batch_count_in_lab',
                'pattern' => 'how many batches in lab',
            ],
            [
                'intent_id' => 'batch_count_in_lab',
                'pattern' => 'batches currently in lab',
            ],
            [
                'intent_id' => 'sample_count_today',
                'pattern' => 'samples today',
            ],
            [
                'intent_id' => 'sample_count_today',
                'pattern' => 'sample count today',
            ],
            [
                'intent_id' => 'sample_count_today',
                'pattern' => 'batches today',
            ],
            [
                'intent_id' => 'sample_count_today',
                'pattern' => 'how many samples today',
            ],
            [
                'intent_id' => 'sample_count_today',
                'pattern' => 'received today',
            ],
            [
                'intent_id' => 'sample_count_today',
                'pattern' => 'batches received today',
            ],
            [
                'intent_id' => 'sample_count_this_week',
                'pattern' => 'samples this week',
            ],
            [
                'intent_id' => 'sample_count_this_week',
                'pattern' => 'sample count this week',
            ],
            [
                'intent_id' => 'sample_count_this_week',
                'pattern' => 'batches this week',
            ],
            [
                'intent_id' => 'sample_count_this_week',
                'pattern' => 'how many samples this week',
            ],
            [
                'intent_id' => 'sample_count_this_week',
                'pattern' => 'received this week',
            ],
            [
                'intent_id' => 'sample_count_this_month',
                'pattern' => 'samples this month',
            ],
            [
                'intent_id' => 'sample_count_this_month',
                'pattern' => 'sample count this month',
            ],
            [
                'intent_id' => 'sample_count_this_month',
                'pattern' => 'batches this month',
            ],
            [
                'intent_id' => 'sample_count_this_month',
                'pattern' => 'how many samples this month',
            ],
            [
                'intent_id' => 'sample_count_this_month',
                'pattern' => 'received this month',
            ],
            [
                'intent_id' => 'samples_rejected',
                'pattern' => 'rejected sample',
            ],
            [
                'intent_id' => 'samples_rejected',
                'pattern' => 'rejected batch',
            ],
            [
                'intent_id' => 'samples_rejected',
                'pattern' => 'cancelled sample',
            ],
            [
                'intent_id' => 'samples_rejected',
                'pattern' => 'samples rejected',
            ],
            [
                'intent_id' => 'samples_rejected',
                'pattern' => 'how many rejected',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'samples pending review',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'pending review',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'awaiting review',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'sample review',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'approval pending',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'pending approval',
            ],
            [
                'intent_id' => 'sample_count_pending_review',
                'pattern' => 'samples awaiting approval',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'samples request review',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'request review',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'review request',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'samples requesting review',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'sample request review',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'request review stage',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'how many samples request review',
            ],
            [
                'intent_id' => 'sample_count_request_review',
                'pattern' => 'how many samples are request review',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'samples approved',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'approved samples',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'how many approved',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'how many samples are approved',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'samples are approved',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'approval count',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'approved batches',
            ],
            [
                'intent_id' => 'sample_count_approved',
                'pattern' => 'batches approved',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'samples verified',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'verified samples',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'how many verified',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'how many samples are verified',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'samples are verified',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'verification count',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'verified batches',
            ],
            [
                'intent_id' => 'sample_count_verified',
                'pattern' => 'batches verified',
            ],
            [
                'intent_id' => 'samples_by_status',
                'pattern' => 'samples by status',
            ],
            [
                'intent_id' => 'samples_by_status',
                'pattern' => 'sample status breakdown',
            ],
            [
                'intent_id' => 'samples_by_status',
                'pattern' => 'status breakdown',
            ],
            [
                'intent_id' => 'samples_by_status',
                'pattern' => 'sample distribution',
            ],
            [
                'intent_id' => 'samples_by_status',
                'pattern' => 'status distribution',
            ],
            [
                'intent_id' => 'sample_count_total',
                'pattern' => 'total samples',
            ],
            [
                'intent_id' => 'sample_count_total',
                'pattern' => 'sample count total',
            ],
            [
                'intent_id' => 'sample_count_total',
                'pattern' => 'total sample count',
            ],
            [
                'intent_id' => 'sample_count_total',
                'pattern' => 'number of samples',
            ],
            [
                'intent_id' => 'sample_count_total',
                'pattern' => 'all samples',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'total samples since start of system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'total samples since start',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'how many samples since start of system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'how many samples since start',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'sample count since start of system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'samples since start of system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'samples since start',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'all time samples',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'absolute samples count',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'how many samples in entire system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'samples in entire system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'entire system samples',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'sample count entire system',
            ],
            [
                'intent_id' => 'sample_count_absolute_all_time',
                'pattern' => 'total samples in entire system',
            ],
            [
                'intent_id' => 'individual_sample_count',
                'pattern' => 'individual sample count',
            ],
            [
                'intent_id' => 'individual_sample_count',
                'pattern' => 'individual samples',
            ],
            [
                'intent_id' => 'individual_sample_count',
                'pattern' => 'aliquots',
            ],
            [
                'intent_id' => 'individual_sample_count',
                'pattern' => 'sample items count',
            ],
            [
                'intent_id' => 'individual_sample_count',
                'pattern' => 'total individual',
            ],
            [
                'intent_id' => 'batch_count_total',
                'pattern' => 'total batches',
            ],
            [
                'intent_id' => 'batch_count_total',
                'pattern' => 'batch count',
            ],
            [
                'intent_id' => 'batch_count_total',
                'pattern' => 'number of batches',
            ],
            [
                'intent_id' => 'batch_count_total',
                'pattern' => 'all batches',
            ],
            [
                'intent_id' => 'latest_received_batches',
                'pattern' => 'latest batch',
            ],
            [
                'intent_id' => 'latest_received_batches',
                'pattern' => 'latest received',
            ],
            [
                'intent_id' => 'latest_received_batches',
                'pattern' => 'recent batch',
            ],
            [
                'intent_id' => 'latest_received_batches',
                'pattern' => 'last received batch',
            ],
            [
                'intent_id' => 'latest_received_batches',
                'pattern' => 'newest batch',
            ],
            [
                'intent_id' => 'latest_received_batches',
                'pattern' => 'recently received',
            ],
            [
                'intent_id' => 'daily_ingestion_trend',
                'pattern' => 'daily ingestion',
            ],
            [
                'intent_id' => 'daily_ingestion_trend',
                'pattern' => 'ingestion trend',
            ],
            [
                'intent_id' => 'daily_ingestion_trend',
                'pattern' => 'registration volume',
            ],
            [
                'intent_id' => 'daily_ingestion_trend',
                'pattern' => 'daily registration',
            ],
            [
                'intent_id' => 'daily_ingestion_trend',
                'pattern' => 'batch registration trend',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'sample type',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'specimen type',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'matrix type',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'sample type distribution',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'types of samples',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'what samples are in lab',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'what samples are in the lab',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'list sample types',
            ],
            [
                'intent_id' => 'sample_type_distribution',
                'pattern' => 'show sample types',
            ],
            [
                'intent_id' => 'inventory_low_stock',
                'pattern' => 'low stock',
            ],
            [
                'intent_id' => 'inventory_low_stock',
                'pattern' => 'below minimum',
            ],
            [
                'intent_id' => 'inventory_low_stock',
                'pattern' => 'reorder level',
            ],
            [
                'intent_id' => 'inventory_low_stock',
                'pattern' => 'stock shortage',
            ],
            [
                'intent_id' => 'inventory_low_stock',
                'pattern' => 'items below minimum',
            ],
            [
                'intent_id' => 'inventory_low_stock',
                'pattern' => 'running low',
            ],
            [
                'intent_id' => 'inventory_order_status',
                'pattern' => 'order status',
            ],
            [
                'intent_id' => 'inventory_order_status',
                'pattern' => 'purchase order',
            ],
            [
                'intent_id' => 'inventory_order_status',
                'pattern' => 'pending delivery',
            ],
            [
                'intent_id' => 'inventory_order_status',
                'pattern' => 'awaiting delivery',
            ],
            [
                'intent_id' => 'inventory_order_status',
                'pattern' => 'order fulfillment',
            ],
            [
                'intent_id' => 'supplier_order_performance',
                'pattern' => 'supplier performance',
            ],
            [
                'intent_id' => 'supplier_order_performance',
                'pattern' => 'supplier fulfillment',
            ],
            [
                'intent_id' => 'supplier_order_performance',
                'pattern' => 'vendor performance',
            ],
            [
                'intent_id' => 'supplier_order_performance',
                'pattern' => 'supplier rate',
            ],
            [
                'intent_id' => 'inventory_stock_by_category',
                'pattern' => 'stock by category',
            ],
            [
                'intent_id' => 'inventory_stock_by_category',
                'pattern' => 'inventory by category',
            ],
            [
                'intent_id' => 'inventory_stock_by_category',
                'pattern' => 'inventory health',
            ],
            [
                'intent_id' => 'inventory_stock_by_category',
                'pattern' => 'category stock',
            ],
            [
                'intent_id' => 'inventory_stock_by_category',
                'pattern' => 'inventory summary',
            ],
            [
                'intent_id' => 'inventory_expiring_soon',
                'pattern' => 'expiring soon',
            ],
            [
                'intent_id' => 'inventory_expiring_soon',
                'pattern' => 'inventory expiring',
            ],
            [
                'intent_id' => 'inventory_expiring_soon',
                'pattern' => 'expiry risk',
            ],
            [
                'intent_id' => 'inventory_expiring_soon',
                'pattern' => 'items expiring',
            ],
            [
                'intent_id' => 'inventory_expiring_soon',
                'pattern' => 'reagent expiry',
            ],
            [
                'intent_id' => 'inventory_expiring_soon',
                'pattern' => 'expiration date',
            ],
            [
                'intent_id' => 'equipment_utilization',
                'pattern' => 'equipment utilization',
            ],
            [
                'intent_id' => 'equipment_utilization',
                'pattern' => 'instrument usage',
            ],
            [
                'intent_id' => 'equipment_utilization',
                'pattern' => 'equipment usage',
            ],
            [
                'intent_id' => 'equipment_utilization',
                'pattern' => 'most used equipment',
            ],
            [
                'intent_id' => 'equipment_utilization',
                'pattern' => 'instrument utilization',
            ],
            [
                'intent_id' => 'equipment_maintenance_schedule',
                'pattern' => 'maintenance schedule',
            ],
            [
                'intent_id' => 'equipment_maintenance_schedule',
                'pattern' => 'upcoming maintenance',
            ],
            [
                'intent_id' => 'equipment_maintenance_schedule',
                'pattern' => 'maintenance due',
            ],
            [
                'intent_id' => 'equipment_maintenance_schedule',
                'pattern' => 'scheduled maintenance',
            ],
            [
                'intent_id' => 'equipment_maintenance_schedule',
                'pattern' => 'next maintenance',
            ],
            [
                'intent_id' => 'equipment_downtime_summary',
                'pattern' => 'equipment downtime',
            ],
            [
                'intent_id' => 'equipment_downtime_summary',
                'pattern' => 'equipment down',
            ],
            [
                'intent_id' => 'equipment_downtime_summary',
                'pattern' => 'non-operational',
            ],
            [
                'intent_id' => 'equipment_downtime_summary',
                'pattern' => 'overdue equipment',
            ],
            [
                'intent_id' => 'equipment_downtime_summary',
                'pattern' => 'broken equipment',
            ],
            [
                'intent_id' => 'equipment_downtime_summary',
                'pattern' => 'equipment overdue',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'active equipment',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'active instruments',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'active equipment count',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'active instruments count',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'how many active instruments',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'how many active equipment',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'how many active equipments',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'number of active equipment',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'number of active equipments',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'total active equipment',
            ],
            [
                'intent_id' => 'equipment_count_active',
                'pattern' => 'total active equipments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'total equipment',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'total equipments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'total equipment count',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'number of equipment',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'number of equipments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'equipment count',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'how many equipment',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'how many equipments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'how many instruments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'total instruments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'all equipment',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'all equipments',
            ],
            [
                'intent_id' => 'equipment_count_total',
                'pattern' => 'all instruments',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'inactive equipment',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'inactive equipments',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'inactive equipment count',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'decommissioned equipment',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'how many inactive equipment',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'how many inactive equipments',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'number of inactive equipment',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'number of inactive equipments',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'total inactive equipment',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'total inactive equipments',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'decommissioned instruments',
            ],
            [
                'intent_id' => 'equipment_count_inactive',
                'pattern' => 'inactive instruments',
            ],
            [
                'intent_id' => 'equipment_maintenance_health',
                'pattern' => 'maintenance health',
            ],
            [
                'intent_id' => 'equipment_maintenance_health',
                'pattern' => 'maintenance status',
            ],
            [
                'intent_id' => 'equipment_maintenance_health',
                'pattern' => 'instrument maintenance state',
            ],
            [
                'intent_id' => 'equipment_maintenance_health',
                'pattern' => 'equipment maintenance state',
            ],
            [
                'intent_id' => 'equipment_maintenance_health',
                'pattern' => 'reliability',
            ],
            [
                'intent_id' => 'equipment_maintenance_health',
                'pattern' => 'equipment reliability',
            ],
            [
                'intent_id' => 'equipment_verification_status',
                'pattern' => 'equipment verification',
            ],
            [
                'intent_id' => 'equipment_verification_status',
                'pattern' => 'verification status',
            ],
            [
                'intent_id' => 'equipment_verification_status',
                'pattern' => 'instrument verification',
            ],
            [
                'intent_id' => 'equipment_verification_status',
                'pattern' => 'last verification',
            ],
            [
                'intent_id' => 'qc_pass_rate',
                'pattern' => 'qc pass rate',
            ],
            [
                'intent_id' => 'qc_pass_rate',
                'pattern' => 'completion rate',
            ],
            [
                'intent_id' => 'qc_pass_rate',
                'pattern' => 'batch completion',
            ],
            [
                'intent_id' => 'qc_pass_rate',
                'pattern' => 'quality pass rate',
            ],
            [
                'intent_id' => 'qc_pass_rate',
                'pattern' => 'qc percentage',
            ],
            [
                'intent_id' => 'qc_drifting_analytes',
                'pattern' => 'drifting analyte',
            ],
            [
                'intent_id' => 'qc_drifting_analytes',
                'pattern' => 'analyte drift',
            ],
            [
                'intent_id' => 'qc_drifting_analytes',
                'pattern' => 'qc drift',
            ],
            [
                'intent_id' => 'qc_drifting_analytes',
                'pattern' => 'unstable analyte',
            ],
            [
                'intent_id' => 'qc_drifting_analytes',
                'pattern' => 'analyte instability',
            ],
            [
                'intent_id' => 'qc_drifting_analytes',
                'pattern' => 'qc stability',
            ],
            [
                'intent_id' => 'capa_pending',
                'pattern' => 'pending capa',
            ],
            [
                'intent_id' => 'capa_pending',
                'pattern' => 'open capa',
            ],
            [
                'intent_id' => 'capa_pending',
                'pattern' => 'corrective action',
            ],
            [
                'intent_id' => 'capa_pending',
                'pattern' => 'capa count',
            ],
            [
                'intent_id' => 'capa_pending',
                'pattern' => 'open corrective',
            ],
            [
                'intent_id' => 'samples_pending_qc',
                'pattern' => 'pending qc',
            ],
            [
                'intent_id' => 'samples_pending_qc',
                'pattern' => 'samples pending qc',
            ],
            [
                'intent_id' => 'samples_pending_qc',
                'pattern' => 'awaiting qc',
            ],
            [
                'intent_id' => 'samples_pending_qc',
                'pattern' => 'qc review',
            ],
            [
                'intent_id' => 'samples_pending_qc',
                'pattern' => 'samples for qc',
            ],
            [
                'intent_id' => 'complaints_trend',
                'pattern' => 'complaint trend',
            ],
            [
                'intent_id' => 'complaints_trend',
                'pattern' => 'complaint volume',
            ],
            [
                'intent_id' => 'complaints_trend',
                'pattern' => 'monthly complaint',
            ],
            [
                'intent_id' => 'complaints_trend',
                'pattern' => 'complaints over time',
            ],
            [
                'intent_id' => 'audit_findings_open',
                'pattern' => 'audit finding',
            ],
            [
                'intent_id' => 'audit_findings_open',
                'pattern' => 'open audit',
            ],
            [
                'intent_id' => 'audit_findings_open',
                'pattern' => 'audit finding open',
            ],
            [
                'intent_id' => 'audit_findings_open',
                'pattern' => 'unresolved audit',
            ],
            [
                'intent_id' => 'tat_overall_average',
                'pattern' => 'average tat',
            ],
            [
                'intent_id' => 'tat_overall_average',
                'pattern' => 'turnaround time',
            ],
            [
                'intent_id' => 'tat_overall_average',
                'pattern' => 'mean tat',
            ],
            [
                'intent_id' => 'tat_overall_average',
                'pattern' => 'tat average',
            ],
            [
                'intent_id' => 'tat_overall_average',
                'pattern' => 'lab turnaround',
            ],
            [
                'intent_id' => 'tat_overall_average',
                'pattern' => 'overall tat',
            ],
            [
                'intent_id' => 'tat_overdue_batches',
                'pattern' => 'overdue batch',
            ],
            [
                'intent_id' => 'tat_overdue_batches',
                'pattern' => 'tat overdue',
            ],
            [
                'intent_id' => 'tat_overdue_batches',
                'pattern' => 'overdue tat',
            ],
            [
                'intent_id' => 'tat_overdue_batches',
                'pattern' => 'batches overdue',
            ],
            [
                'intent_id' => 'tat_overdue_batches',
                'pattern' => 'past deadline',
            ],
            [
                'intent_id' => 'tat_by_analyte',
                'pattern' => 'tat by analyte',
            ],
            [
                'intent_id' => 'tat_by_analyte',
                'pattern' => 'analyte tat',
            ],
            [
                'intent_id' => 'tat_by_analyte',
                'pattern' => 'tat bottleneck',
            ],
            [
                'intent_id' => 'tat_by_analyte',
                'pattern' => 'slowest analyte',
            ],
            [
                'intent_id' => 'tat_by_analyte',
                'pattern' => 'analyte turnaround',
            ],
            [
                'intent_id' => 'tat_sla_compliance',
                'pattern' => 'tat sla',
            ],
            [
                'intent_id' => 'tat_sla_compliance',
                'pattern' => 'sla compliance',
            ],
            [
                'intent_id' => 'tat_sla_compliance',
                'pattern' => 'within sla',
            ],
            [
                'intent_id' => 'tat_sla_compliance',
                'pattern' => 'sla target',
            ],
            [
                'intent_id' => 'tat_sla_compliance',
                'pattern' => 'tat compliance',
            ],
            [
                'intent_id' => 'complaint_count_open',
                'pattern' => 'open complaint',
            ],
            [
                'intent_id' => 'complaint_count_open',
                'pattern' => 'open ticket',
            ],
            [
                'intent_id' => 'complaint_count_open',
                'pattern' => 'complaint count',
            ],
            [
                'intent_id' => 'complaint_count_open',
                'pattern' => 'active complaint',
            ],
            [
                'intent_id' => 'complaint_count_open',
                'pattern' => 'unresolved complaint',
            ],
            [
                'intent_id' => 'complaint_count_open',
                'pattern' => 'complaints open',
            ],
            [
                'intent_id' => 'ticket_backlog_priority',
                'pattern' => 'ticket backlog',
            ],
            [
                'intent_id' => 'ticket_backlog_priority',
                'pattern' => 'ticket priority',
            ],
            [
                'intent_id' => 'ticket_backlog_priority',
                'pattern' => 'support backlog',
            ],
            [
                'intent_id' => 'ticket_backlog_priority',
                'pattern' => 'open tickets by priority',
            ],
            [
                'intent_id' => 'ticket_resolution_time',
                'pattern' => 'resolution time',
            ],
            [
                'intent_id' => 'ticket_resolution_time',
                'pattern' => 'ticket resolution',
            ],
            [
                'intent_id' => 'ticket_resolution_time',
                'pattern' => 'average resolution',
            ],
            [
                'intent_id' => 'ticket_resolution_time',
                'pattern' => 'how long to resolve',
            ],
            [
                'intent_id' => 'tickets_by_department',
                'pattern' => 'tickets by department',
            ],
            [
                'intent_id' => 'tickets_by_department',
                'pattern' => 'department ticket',
            ],
            [
                'intent_id' => 'tickets_by_department',
                'pattern' => 'department backlog',
            ],
            [
                'intent_id' => 'tickets_by_department',
                'pattern' => 'department handling',
            ],
            [
                'intent_id' => 'tickets_by_department',
                'pattern' => 'which department',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'how many analysts',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'analyst count',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'total analysts',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'number of analysts',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'active analysts',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'analysts in system',
            ],
            [
                'intent_id' => 'analyst_count_active',
                'pattern' => 'analysts are in system',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'analyst verification',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'batches verified',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'who verified',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'verification count',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'analyst verified',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'personnel performance',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'staff performance',
            ],
            [
                'intent_id' => 'analyst_verifications',
                'pattern' => 'team performance',
            ],
            [
                'intent_id' => 'analyst_approvals',
                'pattern' => 'analyst approval',
            ],
            [
                'intent_id' => 'analyst_approvals',
                'pattern' => 'batches approved',
            ],
            [
                'intent_id' => 'analyst_approvals',
                'pattern' => 'who approved',
            ],
            [
                'intent_id' => 'analyst_approvals',
                'pattern' => 'approval count',
            ],
            [
                'intent_id' => 'analyst_approvals',
                'pattern' => 'analyst approved',
            ],
            [
                'intent_id' => 'analyst_workload_today',
                'pattern' => 'analyst workload',
            ],
            [
                'intent_id' => 'analyst_workload_today',
                'pattern' => 'analyst activity today',
            ],
            [
                'intent_id' => 'analyst_workload_today',
                'pattern' => 'workload today',
            ],
            [
                'intent_id' => 'analyst_workload_today',
                'pattern' => 'who is working',
            ],
            [
                'intent_id' => 'top_clients_by_volume',
                'pattern' => 'top client',
            ],
            [
                'intent_id' => 'top_clients_by_volume',
                'pattern' => 'top customer',
            ],
            [
                'intent_id' => 'top_clients_by_volume',
                'pattern' => 'biggest client',
            ],
            [
                'intent_id' => 'top_clients_by_volume',
                'pattern' => 'most samples client',
            ],
            [
                'intent_id' => 'top_clients_by_volume',
                'pattern' => 'client volume',
            ],
            [
                'intent_id' => 'top_clients_by_volume',
                'pattern' => 'customer ranking',
            ],
            [
                'intent_id' => 'inactive_clients',
                'pattern' => 'inactive client',
            ],
            [
                'intent_id' => 'inactive_clients',
                'pattern' => 'dormant client',
            ],
            [
                'intent_id' => 'inactive_clients',
                'pattern' => 'churn risk',
            ],
            [
                'intent_id' => 'inactive_clients',
                'pattern' => 'client no submission',
            ],
            [
                'intent_id' => 'inactive_clients',
                'pattern' => 'inactive customer',
            ],
            [
                'intent_id' => 'client_submission_today',
                'pattern' => 'client submission today',
            ],
            [
                'intent_id' => 'client_submission_today',
                'pattern' => 'customer submission today',
            ],
            [
                'intent_id' => 'client_submission_today',
                'pattern' => 'submissions today by client',
            ],
            [
                'intent_id' => 'sla_compliance',
                'pattern' => 'support sla',
            ],
            [
                'intent_id' => 'sla_compliance',
                'pattern' => 'sla rate',
            ],
            [
                'intent_id' => 'sla_compliance',
                'pattern' => 'support compliance',
            ]
            ];

            // 4. Insert intents
            foreach ($intents as $intent) {
                DB::connection($connection)->table('manifest_intents')->insert(array_merge($intent, [
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command?->info('Successfully seeded ' . count($intents) . ' manifest intents.');

            // 5. Insert patterns
            foreach ($patterns as $pattern) {
                DB::connection($connection)->table('manifest_intent_patterns')->insert(array_merge($pattern, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command?->info('Successfully seeded ' . count($patterns) . ' intent patterns.');
        });

        $this->command?->info('====================================================');
        $this->command?->info('AI SEEDING COMPLETED SUCCESSFULLY!');
        $this->command?->info('====================================================');
    }
}
