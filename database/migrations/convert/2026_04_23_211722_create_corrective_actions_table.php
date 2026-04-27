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
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('capa_number')->index('idx_corrective_actions_capa_number_1d1a38e0');
            $table->uuid('non_conformance_id')->index('corrective_actions_non_conformance_id_foreign');
            $table->uuid('capa_category_id')->nullable()->index('corrective_actions_capa_category_id_foreign');
            $table->string('capa_category_name')->nullable();
            $table->uuid('action_type_id')->nullable()->index('corrective_actions_action_type_id_foreign');
            $table->string('action_type_name')->nullable();
            $table->string('title');
            $table->text('description');
            $table->text('expected_outcome')->nullable();
            $table->string('action_owner')->nullable();
            $table->unsignedBigInteger('action_owner_id')->nullable();
            $table->string('department')->nullable();
            $table->date('due_date')->index('idx_corrective_actions_due_date_30cbf99a');
            $table->date('extended_due_date')->nullable();
            $table->text('extension_reason')->nullable();
            $table->date('implementation_date')->nullable();
            $table->uuid('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->uuid('priority_id')->nullable()->index('corrective_actions_priority_id_foreign');
            $table->string('priority_name')->nullable();
            $table->text('implementation_notes')->nullable();
            $table->text('implementation_evidence')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_corrective_actions_created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_corrective_actions_company_id_9e77bc1f');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['capa_number']);
            $table->index(['status_id', 'company_id'], 'idx_corrective_actions_status_id_company_id_7d7f248e');
            $table->foreign(['action_type_id'], 'fk_corrective_actions_action_type_id_ec968186')->references(['id'])->on('capa_action_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['capa_category_id'], 'fk_corrective_actions_capa_category_id_56b8c0c6')->references(['id'])->on('capa_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['non_conformance_id'], 'fk_corrective_actions_non_conformance_id_b8706d63')->references(['id'])->on('non_conformances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['priority_id'], 'fk_corrective_actions_priority_id_d17bed10')->references(['id'])->on('capa_priorities')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_corrective_actions_status_id_8e89b6b0')->references(['id'])->on('capa_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_corrective_actions_company_id_9d86642a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_corrective_actions_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};
