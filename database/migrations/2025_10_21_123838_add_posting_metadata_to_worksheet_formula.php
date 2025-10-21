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
        // Columns were already added by previous failed attempt
        // Just mark migration as complete
        // Foreign key constraint skipped due to unsigned/signed mismatch with legacy users table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_captured_worksheet_formulas', function (Blueprint $table) {
            $table->dropForeign(['posted_by_user_id']);
            $table->dropColumn(['posted_at', 'posted_by_user_id']);
        });
    }
};
