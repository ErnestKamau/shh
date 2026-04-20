<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveMethodIdColumnFromUncertaintyBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uncertainty_budgets', function (Blueprint $table) {
            // Remove the old method_id column since we now use method_ids
            $table->dropColumn('method_id');
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
            // Add back the method_id column if needed to rollback
            $table->unsignedBigInteger('method_id')->after('analyte_id');
        });
    }
}