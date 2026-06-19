<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('sample_submission_requests', 'test_request_form_instance_id')) {
                $table->uuid('test_request_form_instance_id')->nullable()->index()->after('submission_form_instance_id');
                $table->foreign('test_request_form_instance_id')
                    ->references('id')
                    ->on('test_request_form_instances')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sample_submission_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('sample_submission_requests', 'test_request_form_instance_id')) {
                $table->dropForeign(['test_request_form_instance_id']);
                $table->dropColumn('test_request_form_instance_id');
            }
        });
    }
};
