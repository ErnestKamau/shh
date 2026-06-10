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
        Schema::create('test_request_form_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('test_request_form_id')->constrained('test_request_forms')->onDelete('cascade');
            $table->uuid('submission_form_instance_id')->nullable()->index();
            $table->uuid('sampling_schedule_id')->nullable()->index();
            $table->json('form_data')->nullable();
            $table->string('status')->default('draft');
            $table->uuid('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_request_form_instances');
    }
};
