<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interzone_transfers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transfer_scope', 20);
            $table->string('transfer_mode', 20);
            $table->uuid('submission_form_instance_id')->nullable()->index();
            $table->uuid('sample_header_id')->nullable()->index();
            $table->uuid('from_zone_id')->nullable()->index();
            $table->uuid('to_zone_id')->index();
            $table->boolean('report_from_parent_zone')->default(true);
            $table->string('status', 30)->default('completed');
            $table->text('remarks')->nullable();
            $table->uuid('initiated_by')->nullable()->index();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();

            $table->foreign('submission_form_instance_id')
                ->references('id')
                ->on('submission_form_instances')
                ->nullOnDelete();
            $table->foreign('sample_header_id')
                ->references('id')
                ->on('sample_headers')
                ->nullOnDelete();
            $table->foreign('from_zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();
            $table->foreign('to_zone_id')
                ->references('id')
                ->on('zones')
                ->nullOnDelete();
        });

        Schema::create('interzone_transfer_samples', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('interzone_transfer_id')->index();
            $table->uuid('sample_detail_id')->index();
            $table->timestamps();

            $table->foreign('interzone_transfer_id')
                ->references('id')
                ->on('interzone_transfers')
                ->cascadeOnDelete();
            $table->foreign('sample_detail_id')
                ->references('id')
                ->on('sample_details')
                ->cascadeOnDelete();
            $table->unique(['interzone_transfer_id', 'sample_detail_id'], 'interzone_transfer_samples_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interzone_transfer_samples');
        Schema::dropIfExists('interzone_transfers');
    }
};
