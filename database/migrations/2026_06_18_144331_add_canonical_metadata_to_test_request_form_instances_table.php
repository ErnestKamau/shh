<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_request_form_instances', function (Blueprint $table): void {
            $table->string('form_number')->nullable()->after('sampling_schedule_id');
            $table->unsignedInteger('sequence_number')->nullable()->after('form_number');
            $table->uuid('crm_customer_id')->nullable()->index()->after('sequence_number');
            $table->uuid('portal_account_id')->nullable()->index()->after('crm_customer_id');
            $table->string('source_channel', 32)->nullable()->index()->after('portal_account_id');
            $table->uuid('submitted_by')->nullable()->index()->after('source_channel');
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->uuid('zone_id')->nullable()->index()->after('submitted_at');
            $table->uuid('receiving_lab_id')->nullable()->index()->after('zone_id');
            $table->uuid('sample_submission_request_id')->nullable()->index()->after('receiving_lab_id');

            $table->foreign('crm_customer_id')
                ->references('id')
                ->on('crm_customers')
                ->nullOnDelete();
            $table->foreign('sample_submission_request_id')
                ->references('id')
                ->on('sample_submission_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_request_form_instances', function (Blueprint $table): void {
            $table->dropForeign(['crm_customer_id']);
            $table->dropForeign(['sample_submission_request_id']);
            $table->dropColumn([
                'form_number',
                'sequence_number',
                'crm_customer_id',
                'portal_account_id',
                'source_channel',
                'submitted_by',
                'submitted_at',
                'zone_id',
                'receiving_lab_id',
                'sample_submission_request_id',
            ]);
        });
    }
};
