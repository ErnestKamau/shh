<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_customer_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('title')->nullable();
            $table->string('type');
            $table->enum('file_type', ['screenshot', 'document', 'other'])->default('document');
            $table->unsignedInteger('file_size')->nullable();
            $table->string('file_path');
            $table->string('description')->nullable();
            $table->uuid('crm_customer_id')->index('idx_crm_customer_attachments_crm_customer_id');
            $table->string('posted_by');
            $table->boolean('is_delete')->default(false);
            $table->softDeletes();

            $table->foreign('crm_customer_id', 'fk_crm_customer_attachments_crm_customer_id')
                ->references('id')
                ->on('crm_customers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_customer_attachments');
    }
};
