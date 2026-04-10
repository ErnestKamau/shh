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
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'ticket_no')) {
                $table->string('ticket_no')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'time_created')) {
                $table->timestamp('time_created')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'disposition_id')) {
                $table->integer('disposition_id')->nullable(); // complaint type
            }
            // $table->string('complaint_type')->nullable();
            if (!Schema::hasColumn('complaints', 'is_fcr')) {
                $table->boolean('is_fcr')->default(false);
            }
            if (!Schema::hasColumn('complaints', 'issue_source')) {
                $table->string('issue_source')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'ticket_status')) {
                $table->string('ticket_status')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'issue_category')) {
                $table->string('issue_category')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'initial_department')) {
                $table->string('initial_department')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'current_department')) {
                $table->string('current_department')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'customer')) {
                $table->string('customer')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'customer_type')) {
                $table->string('customer_type')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'depot')) {
                $table->string('depot')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'dealer_name')) {
                $table->string('dealer_name')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'dealer_code')) {
                $table->string('dealer_code')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'comments')) {
                $table->text('comments')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'resolution')) {
                $table->text('resolution')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'investigation_and_findings')) {
                $table->text('investigation_and_findings')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'preventive_measures')) {
                $table->text('preventive_measures')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'root_cause')) {
                $table->text('root_cause')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'ticket_products')) {
                $table->text('ticket_products')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'product_id')) {
                $table->integer('product_id')->nullable(); // Foreign key to products table
            }
            if (!Schema::hasColumn('complaints', 'token_received')) {
                $table->string('token_received')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'batch_numbers')) {
                $table->text('batch_numbers')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'resolved_time')) {
                $table->timestamp('resolved_time')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'resolved_by')) {
                $table->string('resolved_by')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'created_by')) {
                $table->string('created_by')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'assigned_to')) {
                $table->string('assigned_to')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'assigned_date')) {
                $table->date('assigned_date')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'age')) {
                $table->integer('age')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'resolution_age')) {
                $table->integer('resolution_age')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'sla_level')) {
                $table->string('sla_level')->nullable();
            }
            if (!Schema::hasColumn('complaints', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('complaints', 'ticket_no')) {
                $columnsToDrop[] = 'ticket_no';
            }
            if (Schema::hasColumn('complaints', 'time_created')) {
                $columnsToDrop[] = 'time_created';
            }
            if (Schema::hasColumn('complaints', 'disposition_id')) {
                $columnsToDrop[] = 'disposition_id';
            }
            if (Schema::hasColumn('complaints', 'is_fcr')) {
                $columnsToDrop[] = 'is_fcr';
            }
            if (Schema::hasColumn('complaints', 'issue_source')) {
                $columnsToDrop[] = 'issue_source';
            }
            if (Schema::hasColumn('complaints', 'ticket_status')) {
                $columnsToDrop[] = 'ticket_status';
            }
            if (Schema::hasColumn('complaints', 'issue_category')) {
                $columnsToDrop[] = 'issue_category';
            }
            if (Schema::hasColumn('complaints', 'initial_department')) {
                $columnsToDrop[] = 'initial_department';
            }
            if (Schema::hasColumn('complaints', 'current_department')) {
                $columnsToDrop[] = 'current_department';
            }
            if (Schema::hasColumn('complaints', 'customer')) {
                $columnsToDrop[] = 'customer';
            }
            if (Schema::hasColumn('complaints', 'customer_type')) {
                $columnsToDrop[] = 'customer_type';
            }
            if (Schema::hasColumn('complaints', 'phone')) {
                $columnsToDrop[] = 'phone';
            }
            if (Schema::hasColumn('complaints', 'depot')) {
                $columnsToDrop[] = 'depot';
            }
            if (Schema::hasColumn('complaints', 'dealer_name')) {
                $columnsToDrop[] = 'dealer_name';
            }
            if (Schema::hasColumn('complaints', 'dealer_code')) {
                $columnsToDrop[] = 'dealer_code';
            }
            if (Schema::hasColumn('complaints', 'comments')) {
                $columnsToDrop[] = 'comments';
            }
            if (Schema::hasColumn('complaints', 'resolution')) {
                $columnsToDrop[] = 'resolution';
            }
            if (Schema::hasColumn('complaints', 'investigation_and_findings')) {
                $columnsToDrop[] = 'investigation_and_findings';
            }
            if (Schema::hasColumn('complaints', 'preventive_measures')) {
                $columnsToDrop[] = 'preventive_measures';
            }
            if (Schema::hasColumn('complaints', 'root_cause')) {
                $columnsToDrop[] = 'root_cause';
            }
            if (Schema::hasColumn('complaints', 'ticket_products')) {
                $columnsToDrop[] = 'ticket_products';
            }
            if (Schema::hasColumn('complaints', 'product_id')) {
                $columnsToDrop[] = 'product_id';
            }
            if (Schema::hasColumn('complaints', 'token_received')) {
                $columnsToDrop[] = 'token_received';
            }
            if (Schema::hasColumn('complaints', 'batch_numbers')) {
                $columnsToDrop[] = 'batch_numbers';
            }
            if (Schema::hasColumn('complaints', 'resolved_time')) {
                $columnsToDrop[] = 'resolved_time';
            }
            if (Schema::hasColumn('complaints', 'resolved_by')) {
                $columnsToDrop[] = 'resolved_by';
            }
            if (Schema::hasColumn('complaints', 'created_by')) {
                $columnsToDrop[] = 'created_by';
            }
            if (Schema::hasColumn('complaints', 'assigned_to')) {
                $columnsToDrop[] = 'assigned_to';
            }
            if (Schema::hasColumn('complaints', 'assigned_date')) {
                $columnsToDrop[] = 'assigned_date';
            }
            if (Schema::hasColumn('complaints', 'age')) {
                $columnsToDrop[] = 'age';
            }
            if (Schema::hasColumn('complaints', 'resolution_age')) {
                $columnsToDrop[] = 'resolution_age';
            }
            if (Schema::hasColumn('complaints', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('complaints', 'sla_level')) {
                $columnsToDrop[] = 'sla_level';
            }
            if (Schema::hasColumn('complaints', 'closed_at')) {
                $columnsToDrop[] = 'closed_at';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
