<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('analysis_methods')) {
            return;
        }

        Schema::table('analysis_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('analysis_methods', 'validation_status')) {
                $table->string('validation_status')->default('pending')->after('method_type_id');
            }

            if (!Schema::hasColumn('analysis_methods', 'is_sampling_method')) {
                $table->boolean('is_sampling_method')->default(0)->after('is_ltm');
            }

            if (!Schema::hasColumn('analysis_methods', 'reference_type_id')) {
                $table->unsignedBigInteger('reference_type_id')->nullable()->after('method_type_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('analysis_methods')) {
            return;
        }

        Schema::table('analysis_methods', function (Blueprint $table) {
            if (Schema::hasColumn('analysis_methods', 'validation_status')) {
                $table->dropColumn('validation_status');
            }
            if (Schema::hasColumn('analysis_methods', 'is_sampling_method')) {
                $table->dropColumn('is_sampling_method');
            }
            if (Schema::hasColumn('analysis_methods', 'reference_type_id')) {
                $table->dropColumn('reference_type_id');
            }
        });
    }
};
