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
        Schema::table('non_conformances', function (Blueprint $table) {
            if (!Schema::hasColumn('non_conformances', 'severity_scale_id')) {
                $table->unsignedBigInteger('severity_scale_id')->nullable()->after('severity_score');
                $table->foreign('severity_scale_id')->references('id')->on('severity_scales')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('non_conformances', 'likelihood_scale_id')) {
                $table->unsignedBigInteger('likelihood_scale_id')->nullable()->after('likelihood_score');
                $table->foreign('likelihood_scale_id')->references('id')->on('likelihood_scales')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('non_conformances', function (Blueprint $table) {
            if (Schema::hasColumn('non_conformances', 'severity_scale_id')) {
                $table->dropForeign(['severity_scale_id']);
                $table->dropColumn('severity_scale_id');
            }
            
            if (Schema::hasColumn('non_conformances', 'likelihood_scale_id')) {
                $table->dropForeign(['likelihood_scale_id']);
                $table->dropColumn('likelihood_scale_id');
            }
        });
    }
};
