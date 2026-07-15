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
        Schema::table('sample_analysis_stages', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_analysis_stages', 'equipment_id')) {
                $table->uuid('equipment_id')->nullable()->after('does_environmental_analysis');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'expected_value_type')) {
                $table->string('expected_value_type')->nullable()->after('equipment_id');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'expected_value')) {
                $table->decimal('expected_value', 14, 4)->nullable()->after('expected_value_type');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'expected_min')) {
                $table->decimal('expected_min', 14, 4)->nullable()->after('expected_value');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'expected_max')) {
                $table->decimal('expected_max', 14, 4)->nullable()->after('expected_min');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'optimum_level')) {
                $table->string('optimum_level')->nullable()->after('expected_max');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'result_nature')) {
                $table->text('result_nature')->nullable()->after('optimum_level');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'reading_frequency')) {
                $table->unsignedTinyInteger('reading_frequency')->nullable()->after('result_nature');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'reading_frequency_interval')) {
                $table->decimal('reading_frequency_interval', 8, 2)->nullable()->after('reading_frequency');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'reading_frequency_schedule')) {
                $table->json('reading_frequency_schedule')->nullable()->after('reading_frequency_interval');
            }
            if (! Schema::hasColumn('sample_analysis_stages', 'reporting_unit')) {
                $table->uuid('reporting_unit')->nullable()->after('reading_frequency_schedule');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_analysis_stages', function (Blueprint $table) {
            $columns = [
                'equipment_id',
                'expected_value_type',
                'expected_value',
                'expected_min',
                'expected_max',
                'optimum_level',
                'result_nature',
                'reading_frequency',
                'reading_frequency_interval',
                'reading_frequency_schedule',
                'reporting_unit',
            ];

            $existing = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn('sample_analysis_stages', $column)
            ));

            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
