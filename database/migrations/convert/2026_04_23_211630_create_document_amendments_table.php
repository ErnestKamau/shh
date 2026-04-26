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
        Schema::create('document_amendments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_id')->index('idx_document_amendments_document_id_38c0c56b');
            $table->integer('amendment_number');
            $table->text('amendment_reason');
            $table->text('amendment_description')->nullable();
            $table->uuid('requested_by')->index('idx_document_amendments_requested_by_1e4ccef1');
            $table->timestamp('requested_at')->useCurrentOnUpdate()->useCurrent()->index('idx_document_amendments_requested_at_e04fe100');
            $table->uuid('authorized_by')->nullable()->index('document_amendments_authorized_by_foreign');
            $table->timestamp('authorized_at')->nullable();
            $table->enum('authorization_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('authorization_comment')->nullable();
            $table->uuid('amended_by')->nullable()->index('document_amendments_amended_by_foreign');
            $table->timestamp('amended_at')->nullable();
            $table->string('file_before_path')->nullable();
            $table->string('file_after_path')->nullable();
            $table->uuid('approved_by')->nullable()->index('document_amendments_approved_by_foreign');
            $table->timestamp('approved_at')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('approval_comment')->nullable();
            $table->enum('status', ['requested', 'authorized', 'amended', 'approved', 'rejected'])->default('requested')->index('idx_document_amendments_requested_845d9b52');
            $table->timestamps();
            $table->foreign(['amended_by'], 'fk_document_amendments_amended_by_16756694')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['approved_by'], 'fk_document_amendments_approved_by_325ebda9')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['authorized_by'], 'fk_document_amendments_authorized_by_70873aab')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['document_id'], 'fk_document_amendments_document_id_12f208c4')->references(['id'])->on('documents')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['requested_by'], 'fk_document_amendments_requested_by_92236343')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
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
