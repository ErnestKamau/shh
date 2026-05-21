<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_invoice') || ! Schema::hasColumn('customer_invoice', 'customer_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE customer_invoice ALTER COLUMN customer_id DROP DEFAULT');
            DB::statement('ALTER TABLE customer_invoice ALTER COLUMN customer_id TYPE uuid USING (NULL::uuid)');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE customer_invoice MODIFY customer_id CHAR(36) NULL');
        } else {
            Schema::table('customer_invoice', function (Blueprint $table): void {
                $table->uuid('customer_id')->nullable()->change();
            });
        }

        Schema::table('customer_invoice', function (Blueprint $table): void {
            if (Schema::hasTable('crm_customers')) {
                $table->foreign('customer_id', 'fk_customer_invoice_crm_customer_id')
                    ->references('id')
                    ->on('crm_customers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_invoice') || ! Schema::hasColumn('customer_invoice', 'customer_id')) {
            return;
        }

        Schema::table('customer_invoice', function (Blueprint $table): void {
            $table->dropForeign('fk_customer_invoice_crm_customer_id');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE customer_invoice ALTER COLUMN customer_id TYPE integer USING (0)');
            DB::statement('ALTER TABLE customer_invoice ALTER COLUMN customer_id SET DEFAULT 0');
        } elseif ($driver === 'mysql') {
            DB::statement('ALTER TABLE customer_invoice MODIFY customer_id INT NOT NULL DEFAULT 0');
        }
    }
};
