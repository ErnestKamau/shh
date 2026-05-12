<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (!Schema::hasColumn('submission_form_instances', 'portal_request_id')) {
                $table->string('portal_request_id', 36)
                    ->nullable()
                    ->after('target_record_id')
                    ->index()
                    ->comment('UUID of the portal SampleSubmissionRequest this instance belongs to, enabling exact template-to-attachment grouping');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submission_form_instances', function (Blueprint $table) {
            if (Schema::hasColumn('submission_form_instances', 'portal_request_id')) {
                $table->dropColumn('portal_request_id');
            }
        });
    }
};
