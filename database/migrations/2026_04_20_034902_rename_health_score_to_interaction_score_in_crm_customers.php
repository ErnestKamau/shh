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
            if (Schema::hasColumn('crm_customers', 'health_score') &&
                !Schema::hasColumn('crm_customers', 'interaction_score')) {
                $table->renameColumn('health_score', 'interaction_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            if (Schema::hasColumn('crm_customers', 'interaction_score') &&
                !Schema::hasColumn('crm_customers', 'health_score')) {
                $table->renameColumn('interaction_score', 'health_score');
            }
        });
    }
};
