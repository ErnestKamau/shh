<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('analysis_elements', 'type_of_analysis_id')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->uuid('type_of_analysis_id')
                ->nullable()
                ->after('analysis_type_id')
                ->index('idx_analysis_elements_type_of_analysis_id');
        });

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->foreign('type_of_analysis_id', 'fk_analysis_elements_type_of_analysis_id')
                ->references('id')
                ->on('types_of_analysis')
                ->nullOnDelete();
        });

        if (\App\TypeOfAnalysis::query()->count() === 0) {
            foreach (\App\TypeOfAnalysis::defaultNames() as $index => $name) {
                \App\TypeOfAnalysis::query()->create([
                    'name' => $name,
                    'sort_order' => $index + 1,
                    'active' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('analysis_elements', 'type_of_analysis_id')) {
            return;
        }

        Schema::table('analysis_elements', function (Blueprint $table) {
            $table->dropForeign('fk_analysis_elements_type_of_analysis_id');
            $table->dropColumn('type_of_analysis_id');
        });
    }
};
