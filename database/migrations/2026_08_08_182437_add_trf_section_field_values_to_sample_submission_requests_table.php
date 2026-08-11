<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sample_submission_requests')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'trf_section_field_values')) {
                $table->json('trf_section_field_values')->nullable()->after('enquiry_sample_configuration');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_submission_requests')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_submission_requests', 'trf_section_field_values')) {
                $table->dropColumn('trf_section_field_values');
            }
        });
    }
};
