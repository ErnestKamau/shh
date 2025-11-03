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
        Schema::table('standards_analytes', function (Blueprint $table) {
            $table->string('matrix_operator')->nullable()->after('value_type');
            $table->string('matrix_value')->nullable()->after('matrix_operator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standards_analytes', function (Blueprint $table) {
            $table->dropColumn(['matrix_operator', 'matrix_value']);
        });
    }
};
