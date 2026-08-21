<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Align supplier_quotes with UUID request entities and RFQ quote fields.
     */
    public function up(): void
    {
        if (! Schema::hasTable('supplier_quotes')) {
            return;
        }

        // Empty or legacy integer FKs cannot hold request entity UUIDs.
        DB::statement('ALTER TABLE supplier_quotes ALTER COLUMN request_id DROP DEFAULT');
        DB::statement('ALTER TABLE supplier_quotes ALTER COLUMN request_item_id DROP DEFAULT');
        DB::statement("ALTER TABLE supplier_quotes ALTER COLUMN request_id TYPE uuid USING NULL");
        DB::statement("ALTER TABLE supplier_quotes ALTER COLUMN request_item_id TYPE uuid USING NULL");

        if (Schema::hasColumn('supplier_quotes', 'registered_by')) {
            DB::statement('ALTER TABLE supplier_quotes ALTER COLUMN registered_by DROP DEFAULT');
            DB::statement("ALTER TABLE supplier_quotes ALTER COLUMN registered_by TYPE uuid USING NULL");
        }

        Schema::table('supplier_quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('supplier_quotes', 'currency_id')) {
                $table->uuid('currency_id')->nullable()->after('quote_amount');
            }
            if (! Schema::hasColumn('supplier_quotes', 'vat_perc')) {
                $table->decimal('vat_perc', 8, 2)->nullable()->default(0)->after('currency_id');
            }
            if (! Schema::hasColumn('supplier_quotes', 'vat_inc')) {
                $table->boolean('vat_inc')->nullable()->default(false)->after('vat_perc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('supplier_quotes')) {
            return;
        }

        Schema::table('supplier_quotes', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_quotes', 'vat_inc')) {
                $table->dropColumn('vat_inc');
            }
            if (Schema::hasColumn('supplier_quotes', 'vat_perc')) {
                $table->dropColumn('vat_perc');
            }
            if (Schema::hasColumn('supplier_quotes', 'currency_id')) {
                $table->dropColumn('currency_id');
            }
        });

        DB::statement('ALTER TABLE supplier_quotes ALTER COLUMN request_id TYPE integer USING NULL');
        DB::statement('ALTER TABLE supplier_quotes ALTER COLUMN request_item_id TYPE integer USING NULL');

        if (Schema::hasColumn('supplier_quotes', 'registered_by')) {
            DB::statement('ALTER TABLE supplier_quotes ALTER COLUMN registered_by TYPE integer USING NULL');
        }
    }
};
