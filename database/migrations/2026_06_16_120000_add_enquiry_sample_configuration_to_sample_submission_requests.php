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

        Schema::table('sample_submission_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('sample_submission_requests', 'enquiry_sample_configuration')) {
                $table->json('enquiry_sample_configuration')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sample_submission_requests')) {
            return;
        }

        Schema::table('sample_submission_requests', function (Blueprint $table) {
            if (Schema::hasColumn('sample_submission_requests', 'enquiry_sample_configuration')) {
                $table->dropColumn('enquiry_sample_configuration');
            }
        });
    }
};
