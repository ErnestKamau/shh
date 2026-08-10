<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('analysis_elements', 'parameter_group_id')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->uuid('parameter_group_id')
                ->nullable()
                ->after('type_of_analysis_id')
                ->index('idx_analysis_elements_parameter_group_id');
        });

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->foreign('parameter_group_id', 'fk_analysis_elements_parameter_group_id')
                ->references('id')
                ->on('parameter_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('analysis_elements', 'parameter_group_id')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_elements_parameter_group_id');
            $table->dropColumn('parameter_group_id');
        });
    }
};
