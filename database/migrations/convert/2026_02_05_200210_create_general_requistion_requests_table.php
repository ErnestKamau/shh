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
        if (Schema::hasTable('general_requistion_requests')) {
            return;
        }
        Schema::create('general_requistion_requests', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code');
            $table->string('requesting_department');
            $table->string('laboratory')->nullable();
            $table->date('date_required');
            $table->uuid('created_by')->nullable()->index('idx_general_requistion_requests_created_by_28397f58');
            $table->string('status');
            $table->text('description')->nullable();
            $table->string('payment_mode', 192)->nullable();
            $table->string('reference', 192)->nullable();
            $table->timestamps();
            $table->boolean('show_pr_approval')->default(false);
            $table->boolean('show_quote_approval')->default(false);
            $table->string('requested_by_1')->nullable();
            $table->string('requested_by_2')->nullable();
            $table->string('checked_by_1')->nullable();
            $table->string('checked_by_2')->nullable();
            $table->string('approved_by_1')->nullable();
            $table->string('approved_by_2')->nullable();
            $table->text('reason_for_approval')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_requistion_requests');
    }
};
