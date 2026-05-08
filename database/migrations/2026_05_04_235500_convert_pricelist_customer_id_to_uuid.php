<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pricelist_customers', function (Blueprint $table): void {
            $table->dropColumn('customer_id');
        });

        Schema::table('pricelist_customers', function (Blueprint $table): void {
            $table->foreignUuid('customer_id')
                ->constrained('crm_customers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pricelist_customers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::table('pricelist_customers', function (Blueprint $table): void {
            $table->integer('customer_id');
        });
    }
};