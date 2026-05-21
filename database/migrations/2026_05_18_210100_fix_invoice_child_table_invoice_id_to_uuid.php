<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_invoice')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        foreach (['invoice_payment_details', 'invoice_details'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'invoice_id')) {
                continue;
            }

            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN invoice_id DROP DEFAULT");
                DB::statement("ALTER TABLE {$table} ALTER COLUMN invoice_id TYPE uuid USING (NULL::uuid)");
            } elseif ($driver === 'mysql') {
                DB::statement("ALTER TABLE {$table} MODIFY invoice_id CHAR(36) NULL");
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->foreign('invoice_id', "fk_{$table}_customer_invoice_id")
                    ->references('id')
                    ->on('customer_invoice')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['invoice_payment_details', 'invoice_details'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'invoice_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropForeign("fk_{$table}_customer_invoice_id");
            });

            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN invoice_id TYPE integer USING (0)");
                DB::statement("ALTER TABLE {$table} ALTER COLUMN invoice_id SET DEFAULT 0");
            } elseif ($driver === 'mysql') {
                DB::statement("ALTER TABLE {$table} MODIFY invoice_id INT NULL DEFAULT 0");
            }
        }
    }
};
