<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentAmendmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('document_amendments', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('document_id');
            $table->integer('amendment_number');
            $table->text('amendment_reason');
            $table->text('amendment_description')->nullable();
            $table->bigInteger('requested_by');
            $table->timestamp('requested_at');
            $table->bigInteger('authorized_by')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->enum('authorization_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('authorization_comment')->nullable();
            $table->bigInteger('amended_by')->nullable();
            $table->timestamp('amended_at')->nullable();
            $table->string('file_before_path')->nullable();
            $table->string('file_after_path')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('approval_comment')->nullable();
            $table->enum('status', ['requested', 'authorized', 'amended', 'approved', 'rejected'])->default('requested');
            $table->timestamps();

            // Foreign keys
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('authorized_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('amended_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('document_id');
            $table->index('status');
            $table->index('requested_by');
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('document_amendments');
    }
}

