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
            $table->id();
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
            $table->integer('company_id')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'code']);
        });

        \DB::table('workflow_actions')->insert([
            [
                'name' => 'Approve',
                'code' => 'APPROVE',
                'description' => 'Approve and move to next workflow step.',
                'icon' => 'mdi-check-circle',
                'color_code' => '#28a745',
                'badge_class' => 'success',
                'requires_remarks' => true,
                'min_remarks_length' => 10,
                'requires_target_status' => true,
                'is_active' => true,
                'order_index' => 1,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reject',
                'code' => 'REJECT',
                'description' => 'Reject the current workflow action.',
                'icon' => 'mdi-close-circle',
                'color_code' => '#dc3545',
                'badge_class' => 'danger',
                'requires_remarks' => true,
                'min_remarks_length' => 10,
                'requires_target_status' => false,
                'is_active' => true,
                'order_index' => 2,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Return',
                'code' => 'RETURN',
                'description' => 'Return item to a previous step for correction.',
                'icon' => 'mdi-undo',
                'color_code' => '#ffc107',
                'badge_class' => 'warning',
                'requires_remarks' => true,
                'min_remarks_length' => 10,
                'requires_target_status' => true,
                'is_active' => true,
                'order_index' => 3,
                'company_id' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
    }
};
