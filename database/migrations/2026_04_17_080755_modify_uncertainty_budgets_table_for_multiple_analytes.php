<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUncertaintyBudgetsTableForMultipleAnalytes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uncertainty_budgets', function (Blueprint $table) {
            // Add new column for comma-separated method IDs
            $table->text('method_ids')->after('method_id');
            
            // Keep the old method_id column for backward compatibility
            // We'll populate it with the first method ID from the comma-separated list
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uncertainty_budgets', function (Blueprint $table) {
            $table->dropColumn('method_ids');
        });
    }
}