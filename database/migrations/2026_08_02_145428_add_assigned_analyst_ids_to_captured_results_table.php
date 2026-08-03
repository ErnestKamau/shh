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
        Schema::table('captured_results', function (Blueprint $table): void {
            $table->json('assigned_analyst_ids')->nullable()->after('operator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('captured_results', function (Blueprint $table): void {
            $table->dropColumn('assigned_analyst_ids');
        });
    }
};
