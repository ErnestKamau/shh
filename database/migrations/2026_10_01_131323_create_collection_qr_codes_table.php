<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pre-collection container QR codes printed from a TRF or a System Planner schedule.
     * Scanning resolves to the linked sample's latest official Test Report.
     */
    public function up(): void
    {
        Schema::create('collection_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->unique();
            $table->string('sampling_schedule_id')->nullable();
            $table->string('submission_form_instance_id')->nullable();
            $table->unsignedSmallInteger('row_index')->nullable();
            $table->unsignedSmallInteger('slot_no')->default(1);
            $table->string('batch_id')->nullable();
            $table->string('sample_detail_id')->nullable();
            $table->string('status', 20)->default('unlinked');
            $table->timestamp('linked_at')->nullable();
            $table->uuid('generated_by')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['submission_form_instance_id', 'row_index'],
                'collection_qr_codes_instance_row_unique'
            );
            $table->index('sampling_schedule_id');
            $table->index('submission_form_instance_id');
            $table->index('sample_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_qr_codes');
    }
};
