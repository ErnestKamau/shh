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
        Schema::table('crm_evaluation_metrics', function (Blueprint $table) {
            $table->json('rating_labels')->nullable()->after('max_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_evaluation_metrics', function (Blueprint $table) {
            $table->dropColumn('rating_labels');
        });
    }
};
