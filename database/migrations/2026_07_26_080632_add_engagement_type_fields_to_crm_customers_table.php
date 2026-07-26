<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('crm_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_customers', 'engagement_type')) {
                $table->string('engagement_type', 20)->nullable()->after('customer_type');
            }
            if (! Schema::hasColumn('crm_customers', 'requires_sampling')) {
                $table->boolean('requires_sampling')->default(false)->after('engagement_type');
            }
            if (! Schema::hasColumn('crm_customers', 'is_one_time')) {
                $table->boolean('is_one_time')->default(false)->after('requires_sampling');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('crm_customers', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('crm_customers', 'engagement_type') ? 'engagement_type' : null,
                Schema::hasColumn('crm_customers', 'requires_sampling') ? 'requires_sampling' : null,
                Schema::hasColumn('crm_customers', 'is_one_time') ? 'is_one_time' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
