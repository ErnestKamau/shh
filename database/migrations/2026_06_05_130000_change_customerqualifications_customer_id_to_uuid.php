<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customerqualifications')) {
            return;
        }

        $customerIdType = DB::selectOne(
            "SELECT data_type FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = 'customerqualifications'
               AND column_name = 'customer_id'"
        );

        if (! $customerIdType || $customerIdType->data_type === 'uuid') {
            $this->ensureCustomerForeignKey();

            return;
        }

        $existingFk = DB::selectOne(
            "SELECT 1 FROM information_schema.table_constraints
             WHERE table_schema = current_schema()
               AND table_name = 'customerqualifications'
               AND constraint_name = 'fk_customerqualifications_customer_id'
               AND constraint_type = 'FOREIGN KEY'"
        );

        if ($existingFk) {
            Schema::table('customerqualifications', function (Blueprint $table) {
                $table->dropForeign('fk_customerqualifications_customer_id');
            });
        }

        // Legacy integer customer IDs cannot map to UUID crm_customers; clear before type change.
        DB::statement('ALTER TABLE customerqualifications ALTER COLUMN customer_id DROP DEFAULT');
        DB::statement(
            'ALTER TABLE customerqualifications
             ALTER COLUMN customer_id TYPE uuid
             USING (NULL::uuid)'
        );

        $this->ensureCustomerForeignKey();
    }

    private function ensureCustomerForeignKey(): void
    {
        $existingFk = DB::selectOne(
            "SELECT 1 FROM information_schema.table_constraints
             WHERE table_schema = current_schema()
               AND table_name = 'customerqualifications'
               AND constraint_name = 'fk_customerqualifications_customer_id'
               AND constraint_type = 'FOREIGN KEY'"
        );

        if ($existingFk || ! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('customerqualifications', function (Blueprint $table) {
            $table->foreign('customer_id', 'fk_customerqualifications_customer_id')
                ->references('id')
                ->on('crm_customers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Irreversible without data loss once UUID CRM customers are stored.
    }
};
