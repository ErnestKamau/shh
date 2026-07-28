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
        Schema::table('submission_forms', function (Blueprint $table) {
            if (! Schema::hasColumn('submission_forms', 'is_hidden_from_rft')) {
                $table->boolean('is_hidden_from_rft')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_forms', function (Blueprint $table) {
            if (Schema::hasColumn('submission_forms', 'is_hidden_from_rft')) {
                $table->dropColumn('is_hidden_from_rft');
            }
        });
    }
};
