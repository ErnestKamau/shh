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
        if (Schema::hasTable('audit_module_findings')) {
            return;
        }
        Schema::create('audit_module_findings', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('finding_number')->index('idx_audit_module_findings_finding_number_c08639b0');
            $table->uuid('audit_id');
            $table->uuid('finding_category_id')->nullable()->index('idx_audit_module_findings_finding_category_id_87bf058c');
            $table->string('finding_category_name')->nullable();
            $table->string('iso_clause')->nullable();
            $table->string('sop_reference')->nullable();
            $table->text('requirement')->nullable();
            $table->text('observation')->nullable();
            $table->text('objective_evidence')->nullable();
            $table->uuid('risk_level_id')->nullable()->index('idx_audit_module_findings_risk_level_id_28feba9e');
            $table->string('risk_level_name')->nullable();
            $table->string('responsible_person')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->date('response_due_date')->nullable();
            $table->uuid('status_id')->nullable()->index('idx_audit_module_findings_status_id_ed878e4b');
            $table->string('status_name')->nullable();
            $table->integer('order_index')->default(0);
            $table->uuid('created_by')->nullable()->index('idx_audit_module_findings_created_by_a7110d8b');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['audit_id', 'status_id'], 'idx_audit_module_findings_audit_id_status_id_9ec42757');
            $table->unique(['finding_number']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_module_findings');
    }
};
