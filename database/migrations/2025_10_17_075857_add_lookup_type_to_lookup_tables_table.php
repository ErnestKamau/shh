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
        Schema::table('lookup_tables', function (Blueprint $table) {
            $table->enum('lookup_type', ['key_value_comparison', 'range_based'])
                  ->default('key_value_comparison')
                  ->after('value_column');
            $table->string('range_variable_name')->nullable()->after('lookup_type');
            $table->string('value_interpretation_column')->nullable()->after('range_variable_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lookup_tables', function (Blueprint $table) {
            $table->dropColumn(['lookup_type', 'range_variable_name', 'value_interpretation_column']);
        });
    }
};
