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
        Schema::table('sample_worksheet_formular_step_data', function (Blueprint $table) {
            $table->unsignedBigInteger('overridden_lookup_table_id')->nullable()->after('step_value');
            
            $table->foreign('overridden_lookup_table_id', 'fk_override_lookup')
                  ->references('id')
                  ->on('lookup_tables')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_worksheet_formular_step_data', function (Blueprint $table) {
            $table->dropForeign('fk_override_lookup');
            $table->dropColumn('overridden_lookup_table_id');
        });
    }
};
