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
        Schema::table('captured_results', function (Blueprint $table) {
            $table->unsignedBigInteger('procedure_worksheet_id')->nullable()->after('method_sequence_id');
            $table->boolean('has_procedure_worksheet')->default(false)->after('procedure_worksheet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_results', function (Blueprint $table) {
            $table->dropColumn(['procedure_worksheet_id', 'has_procedure_worksheet']);
        });
    }
};
