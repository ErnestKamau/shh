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
        if (Schema::hasTable('crm_customer_contracts')) {
            Schema::table('crm_customer_contracts', function (Blueprint $table): void {
                if (! Schema::hasColumn('crm_customer_contracts', 'contract_scope')) {
                    $table->string('contract_scope', 32)->nullable()->after('valid_to');
                }
                if (! Schema::hasColumn('crm_customer_contracts', 'is_scheduled_sampling')) {
                    $table->boolean('is_scheduled_sampling')->default(false)->after('contract_scope');
                }
                if (! Schema::hasColumn('crm_customer_contracts', 'default_collection_method')) {
                    $table->string('default_collection_method', 32)->nullable()->after('is_scheduled_sampling');
                }
            });
        }

        if (Schema::hasTable('crm_customers')) {
            Schema::table('crm_customers', function (Blueprint $table): void {
                if (! Schema::hasColumn('crm_customers', 'contract_scope')) {
                    $table->string('contract_scope', 32)->nullable()->after('contract_valid_to');
                }
                if (! Schema::hasColumn('crm_customers', 'is_scheduled_sampling')) {
                    $table->boolean('is_scheduled_sampling')->default(false)->after('contract_scope');
                }
                if (! Schema::hasColumn('crm_customers', 'default_collection_method')) {
                    $table->string('default_collection_method', 32)->nullable()->after('is_scheduled_sampling');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('crm_customer_contracts')) {
            Schema::table('crm_customer_contracts', function (Blueprint $table): void {
                $columns = array_values(array_filter([
                    Schema::hasColumn('crm_customer_contracts', 'contract_scope') ? 'contract_scope' : null,
                    Schema::hasColumn('crm_customer_contracts', 'is_scheduled_sampling') ? 'is_scheduled_sampling' : null,
                    Schema::hasColumn('crm_customer_contracts', 'default_collection_method') ? 'default_collection_method' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('crm_customers')) {
            Schema::table('crm_customers', function (Blueprint $table): void {
                $columns = array_values(array_filter([
                    Schema::hasColumn('crm_customers', 'contract_scope') ? 'contract_scope' : null,
                    Schema::hasColumn('crm_customers', 'is_scheduled_sampling') ? 'is_scheduled_sampling' : null,
                    Schema::hasColumn('crm_customers', 'default_collection_method') ? 'default_collection_method' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
