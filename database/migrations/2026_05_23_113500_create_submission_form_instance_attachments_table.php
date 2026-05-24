<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_form_instance_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_form_instance_id');
            $table->string('file_path');
            $table->string('original_name');
            $table->uuid('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('submission_form_instance_id', 'fk_sfi_attachments_instance_id')
                  ->references('id')->on('submission_form_instances')
                  ->onDelete('cascade');
                  
            $table->foreign('uploaded_by', 'fk_sfi_attachments_user_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_form_instance_attachments');
    }
};
