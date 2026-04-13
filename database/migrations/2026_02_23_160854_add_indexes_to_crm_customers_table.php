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
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->index(['company_id', 'active']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'active']);
            $table->dropIndex(['name']);
        });
    }
};
