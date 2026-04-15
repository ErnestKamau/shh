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
        $driver = DB::getDriverName();

        // Robust driver detection: handle pgsql, pgsql_ai, postgres, postgresql
        $isPgsql = in_array($driver, ['pgsql', 'pgsql_ai', 'postgres', 'postgresql']);

        if ($isPgsql) {
            // PostgreSQL versions of views
            DB::statement("
                CREATE OR REPLACE VIEW v_inventory_risk_detail AS
                SELECT 
                    i.id as item_id,
                    sc.name as item_name,
                    i.batch_code,
                    i.expiry as expiry_date,
                    (i.expiry - CURRENT_DATE) as days_until_expiry,
                    (i.stock_in - i.stock_out) as quantity_in_stock,
                    s.name as location_name,
                    (CURRENT_DATE - i.updated_at::date) as days_since_last_movement,
                    sc.minimum_level as reorder_level,
                    100 as reorder_quantity,
                    i.inventory_sub_category_id,
                    i.inventory_store_id,
                    0 as physical_count,
                    (i.stock_in - i.stock_out) as system_count,
                    0 as variance_quantity,
                    0 as variance_percentage,
                    NULL as last_stock_take_date,
                    NULL as last_reorder_date,
                    0 as days_since_last_reorder,
                    0 as value_at_risk
                FROM inventory_items i
                JOIN inventory_sub_categories sc ON sc.id = i.inventory_sub_category_id
                JOIN inventory_stores s ON s.id = i.inventory_store_id
                WHERE (i.stock_in - i.stock_out) > 0
            ");

            DB::statement("
                CREATE OR REPLACE VIEW v_inventory_position_summary AS
                SELECT 
                    sc.id as inventory_sub_category_id,
                    s.id as inventory_store_id,
                    sc.name as item_name,
                    sc.code as item_code,
                    SUM(i.stock_in - i.stock_out)::integer as available_qty,
                    0::integer as pending_qty,
                    sc.minimum_level,
                    (SUM(i.stock_in - i.stock_out) < sc.minimum_level)::boolean as below_minimum,
                    SUM(CASE WHEN i.expiry < CURRENT_DATE THEN (i.stock_in - i.stock_out) ELSE 0 END)::integer as expired_qty,
                    SUM(CASE WHEN i.expiry BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '30 days' THEN (i.stock_in - i.stock_out) ELSE 0 END)::integer as near_expiry_qty,
                    MAX(i.updated_at) as refreshed_at
                FROM inventory_items i
                JOIN inventory_sub_categories sc ON sc.id = i.inventory_sub_category_id
                JOIN inventory_stores s ON s.id = i.inventory_store_id
                GROUP BY sc.id, s.id, sc.name, sc.code, sc.minimum_level
            ");

            DB::statement("
                CREATE OR REPLACE VIEW v_inventory_risk_summary AS
                SELECT 
                    inventory_store_id,
                    COUNT(CASE WHEN below_minimum = true THEN 1 END) as items_below_minimum,
                    COUNT(CASE WHEN near_expiry_qty > 0 THEN 1 END) as items_near_expiry,
                    COUNT(CASE WHEN expired_qty > 0 THEN 1 END) as items_expired,
                    SUM(available_qty)::integer as total_available_qty,
                    MAX(refreshed_at) as refreshed_at
                FROM v_inventory_position_summary
                GROUP BY inventory_store_id
            ");
        } else {
            // MySQL versions of views (original)
            DB::statement("
                CREATE OR REPLACE VIEW v_inventory_risk_detail AS
                SELECT 
                    i.id as item_id,
                    sc.name as item_name,
                    i.batch_code,
                    i.expiry as expiry_date,
                    DATEDIFF(i.expiry, CURDATE()) as days_until_expiry,
                    (i.stock_in - i.stock_out) as quantity_in_stock,
                    s.name as location_name,
                    DATEDIFF(CURDATE(), i.updated_at) as days_since_last_movement,
                    sc.minimum_level as reorder_level,
                    100 as reorder_quantity,
                    i.inventory_sub_category_id,
                    i.inventory_store_id,
                    0 as physical_count,
                    (i.stock_in - i.stock_out) as system_count,
                    0 as variance_quantity,
                    0 as variance_percentage,
                    NULL as last_stock_take_date,
                    NULL as last_reorder_date,
                    0 as days_since_last_reorder,
                    0 as value_at_risk
                FROM inventory_items i
                JOIN inventory_sub_categories sc ON sc.id = i.inventory_sub_category_id
                JOIN inventory_stores s ON s.id = i.inventory_store_id
                WHERE (i.stock_in - i.stock_out) > 0
            ");

            DB::statement("
                CREATE OR REPLACE VIEW v_inventory_position_summary AS
                SELECT 
                    sc.id as inventory_sub_category_id,
                    s.id as inventory_store_id,
                    sc.name as item_name,
                    sc.code as item_code,
                    SUM(i.stock_in - i.stock_out) as available_qty,
                    0 as pending_qty,
                    sc.minimum_level,
                    (SUM(i.stock_in - i.stock_out) < sc.minimum_level) as below_minimum,
                    SUM(CASE WHEN i.expiry < CURDATE() THEN (i.stock_in - i.stock_out) ELSE 0 END) as expired_qty,
                    SUM(CASE WHEN i.expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN (i.stock_in - i.stock_out) ELSE 0 END) as near_expiry_qty,
                    MAX(i.updated_at) as refreshed_at
                FROM inventory_items i
                JOIN inventory_sub_categories sc ON sc.id = i.inventory_sub_category_id
                JOIN inventory_stores s ON s.id = i.inventory_store_id
                GROUP BY sc.id, s.id, sc.name, sc.code, sc.minimum_level
            ");

            DB::statement("
                CREATE OR REPLACE VIEW v_inventory_risk_summary AS
                SELECT 
                    inventory_store_id,
                    COUNT(CASE WHEN below_minimum = 1 THEN 1 END) as items_below_minimum,
                    COUNT(CASE WHEN near_expiry_qty > 0 THEN 1 END) as items_near_expiry,
                    COUNT(CASE WHEN expired_qty > 0 THEN 1 END) as items_expired,
                    SUM(available_qty) as total_available_qty,
                    MAX(refreshed_at) as refreshed_at
                FROM v_inventory_position_summary
                GROUP BY inventory_store_id
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_inventory_risk_summary");
        DB::statement("DROP VIEW IF EXISTS v_inventory_position_summary");
        DB::statement("DROP VIEW IF EXISTS v_inventory_risk_detail");
    }
};

