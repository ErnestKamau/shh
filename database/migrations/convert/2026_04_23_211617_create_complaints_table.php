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
        Schema::create('complaints', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->timestamp('last_synced_at')->nullable()->index('idx_complaints_last_synced_at_7a58c93f');
            $table->boolean('sync_failed')->default(false)->index('idx_complaints_sync_failed_5f547664');
            $table->text('sync_error')->nullable();
            $table->uuid('complaint_id')->index('idx_complaints_complaint_id_e2f906da');
            $table->text('description');
            $table->string('priority');
            $table->string('received_from');
            $table->string('registered_by');
            $table->integer('complaint_workflow');
            $table->boolean('is_closed')->default(false);
            $table->string('edited_by')->nullable();
            $table->boolean('rejected')->default(false);
            $table->string('type')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('reject_workflow')->nullable();
            $table->longText('closure_recipient_emails')->nullable();
            $table->dateTime('closure_sent_at')->nullable();
            $table->integer('client_id')->default(0);
            $table->string('ticket_no')->nullable()->unique();
            $table->string('developer_ticket_no')->nullable();
            $table->unsignedBigInteger('developer_ticket_id')->nullable()->index('idx_complaints_developer_ticket_id_d97908f4');
            $table->timestamp('time_created')->nullable();
            $table->integer('disposition_id')->nullable();
            $table->boolean('is_fcr')->default(false);
            $table->string('issue_source')->nullable();
            $table->string('ticket_status')->nullable();
            $table->string('issue_category')->nullable();
            $table->string('initial_department')->nullable();
            $table->string('current_department')->nullable();
            $table->string('customer')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('phone')->nullable();
            $table->string('depot')->nullable();
            $table->string('dealer_name')->nullable();
            $table->string('dealer_code')->nullable();
            $table->text('comments')->nullable();
            $table->text('resolution')->nullable();
            $table->text('investigation_and_findings')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('ticket_products')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('token_received')->nullable();
            $table->text('batch_numbers')->nullable();
            $table->timestamp('resolved_time')->nullable();
            $table->string('resolution_sla_status', 50)->nullable();
            $table->string('resolved_by')->nullable();
            $table->string('created_by')->nullable();
            $table->string('assigned_to')->nullable();
            $table->date('assigned_date')->nullable();
            $table->integer('age')->nullable();
            $table->integer('resolution_age')->nullable();
            $table->string('status')->nullable();
            $table->string('sla_level')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->string('first_response_sla_status', 50)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->enum('submitted_from', ['ticketing_system', 'jasiri_lims', 'other'])->default('ticketing_system');
            $table->uuid('ticket_category_id')->nullable()->index('complaints_ticket_category_id_foreign');
            $table->boolean('is_internal_note')->default(false);
            $table->uuid('escalated_to_user_id')->nullable()->index('complaints_escalated_to_user_id_foreign');
            $table->uuid('escalated_from_user_id')->nullable()->index('complaints_escalated_from_user_id_foreign');
            $table->text('escalation_reason')->nullable();
            $table->string('mode_of_delivery')->nullable();
            $table->string('received_by')->nullable();
            $table->string('received_from_type')->nullable();
            $table->boolean('is_lab_related')->default(false);
            $table->text('nature_of_complaint')->nullable();
            $table->string('test_item_report_serial_no')->nullable();
            $table->uuid('intake_approved_by')->nullable()->index('complaints_intake_approved_by_foreign');
            $table->timestamp('intake_approved_at')->nullable();
            $table->uuid('closed_by')->nullable()->index('complaints_closed_by_foreign');
            $table->date('date_closed')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('title_position')->nullable();
            $table->string('test_item')->nullable();
            $table->string('report_serial_no')->nullable();
            $table->foreign(['closed_by'], 'fk_complaints_closed_by_88d82921')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['escalated_from_user_id'], 'fk_complaints_escalated_from_user_id_b3bcc4ed')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['escalated_to_user_id'], 'fk_complaints_escalated_to_user_id_71f14b27')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['intake_approved_by'], 'fk_complaints_intake_approved_by_cf863675')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ticket_category_id'], 'fk_complaints_ticket_category_id_6c44710b')->references(['id'])->on('ticket_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['complaint_id'], 'fk_complaints_complaint_id_68f339c0')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
