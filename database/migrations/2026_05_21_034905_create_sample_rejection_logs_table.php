<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_rejection_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_form_instance_id')->nullable()->index();
            $table->uuid('sample_submission_request_id')->nullable()->index();
            $table->string('request_no')->nullable();
            $table->string('client_name');
            $table->date('date_sample_received')->nullable();
            $table->string('type_of_sample')->nullable();
            $table->unsignedInteger('number_of_samples')->default(1);
            $table->json('reasons');
            $table->text('integrity_notice');
            $table->uuid('rejected_by')->nullable();
            $table->timestamp('rejected_at');
            $table->string('pdf_path')->nullable();
            $table->text('internal_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_rejection_logs');
    }
};
