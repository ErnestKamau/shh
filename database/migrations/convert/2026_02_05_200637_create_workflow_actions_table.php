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
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color_code')->nullable();
            $table->string('badge_class')->nullable();
            $table->boolean('requires_remarks')->default(true);
            $table->integer('min_remarks_length')->default(10);
            $table->boolean('requires_target_status')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->uuid('company_id')->nullable()->index('idx_workflow_actions_company_id_a9b88cee');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active'], 'idx_workflow_actions_company_id_is_active_df36003c');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
    }
};
