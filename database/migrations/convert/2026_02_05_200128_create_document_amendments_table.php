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
        if (Schema::hasTable('document_amendments')) {
            return;
        }
        Schema::create('document_amendments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_id')->index('idx_document_amendments_document_id_a42f3baa');
            $table->integer('amendment_number');
            $table->text('amendment_reason');
            $table->text('amendment_description')->nullable();
            $table->uuid('requested_by')->index('idx_document_amendments_requested_by_cbf3bbfa');
            $table->timestamp('requested_at')->useCurrentOnUpdate()->useCurrent()->index('idx_document_amendments_requested_at_d7d5235b');
            $table->uuid('authorized_by')->nullable()->index('idx_document_amendments_authorized_by_0efabade');
            $table->timestamp('authorized_at')->nullable();
            $table->enum('authorization_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('authorization_comment')->nullable();
            $table->uuid('amended_by')->nullable()->index('idx_document_amendments_amended_by_086e5663');
            $table->timestamp('amended_at')->nullable();
            $table->string('file_before_path')->nullable();
            $table->string('file_after_path')->nullable();
            $table->uuid('approved_by')->nullable()->index('idx_document_amendments_approved_by_783da2fe');
            $table->timestamp('approved_at')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('approval_comment')->nullable();
            $table->enum('status', ['requested', 'authorized', 'amended', 'approved', 'rejected'])->default('requested')->index('idx_document_amendments_requested_b96866ab');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_amendments');
    }
};
