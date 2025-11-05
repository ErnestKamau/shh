<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing data
        $customers = DB::table('crm_customers')
            ->whereNotNull('zoho_customer_id')
            ->where('zoho_customer_id', '!=', '')
            ->get(['id', 'zoho_customer_id']);

        // Store existing values temporarily
        $existingData = [];
        foreach ($customers as $customer) {
            if ($customer->zoho_customer_id) {
                $existingData[$customer->id] = [$customer->zoho_customer_id];
            }
        }

        // Modify the column to JSON
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->json('zoho_customer_id')->nullable()->change();
        });

        // Restore data as JSON arrays
        foreach ($existingData as $customerId => $zohoIds) {
            DB::table('crm_customers')
                ->where('id', $customerId)
                ->update(['zoho_customer_id' => json_encode($zohoIds)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get current data
        $customers = DB::table('crm_customers')
            ->whereNotNull('zoho_customer_id')
            ->get(['id', 'zoho_customer_id']);

        // Store first ID from each JSON array
        $existingData = [];
        foreach ($customers as $customer) {
            if ($customer->zoho_customer_id) {
                $ids = json_decode($customer->zoho_customer_id, true);
                if (is_array($ids) && count($ids) > 0) {
                    $existingData[$customer->id] = $ids[0];
                }
            }
        }

        // Convert back to bigint
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('zoho_customer_id')->nullable()->change();
        });

        // Restore first ID only
        foreach ($existingData as $customerId => $zohoId) {
            DB::table('crm_customers')
                ->where('id', $customerId)
                ->update(['zoho_customer_id' => $zohoId]);
        }
    }
};
