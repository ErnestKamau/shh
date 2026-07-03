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
        if (Schema::hasTable('corrective_actions')) {
            return;
        }
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('capa_number')->index('idx_corrective_actions_capa_number_2f9f8b6e');
            $table->uuid('non_conformance_id')->index('idx_corrective_actions_non_conformance_id_a60c6668');
            $table->uuid('capa_category_id')->nullable()->index('idx_corrective_actions_capa_category_id_67ced230');
            $table->string('capa_category_name')->nullable();
            $table->uuid('action_type_id')->nullable()->index('idx_corrective_actions_action_type_id_544f2bee');
            $table->string('action_type_name')->nullable();
            $table->string('title');
            $table->text('description');
            $table->text('expected_outcome')->nullable();
            $table->string('action_owner')->nullable();
            $table->unsignedBigInteger('action_owner_id')->nullable();
            $table->string('department')->nullable();
            $table->date('due_date')->index('idx_corrective_actions_due_date_150d15bf');
            $table->date('extended_due_date')->nullable();
            $table->text('extension_reason')->nullable();
            $table->date('implementation_date')->nullable();
            $table->uuid('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->uuid('priority_id')->nullable()->index('idx_corrective_actions_priority_id_d1f7da7c');
            $table->string('priority_name')->nullable();
            $table->text('implementation_notes')->nullable();
            $table->text('implementation_evidence')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_corrective_actions_created_by_ba2eb404');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_corrective_actions_company_id_027466e4');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['capa_number']);
            $table->index(['status_id', 'company_id'], 'idx_corrective_actions_status_id_company_id_79f5bbb3');

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
