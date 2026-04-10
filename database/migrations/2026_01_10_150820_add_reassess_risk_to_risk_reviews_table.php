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
        Schema::table('risk_reviews', function (Blueprint $table) {
            $table->boolean('reassess_risk')->default(false)->after('action_required_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_reviews', function (Blueprint $table) {
            $table->dropColumn('reassess_risk');
        });
    }
};
