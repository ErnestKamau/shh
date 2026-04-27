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
        Schema::create('batch_labsection_approval', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('batch_id')->index('idx_batch_labsection_approval_batch_id');
            $table->uuid('user_id')->index('idx_batch_labsection_approval_user_id_9a7bb4b5');
            $table->string('title');
            $table->string('lab_section_ids');
            $table->boolean('status')->nullable()->default(false);
            $table->string('batch_status', 100)->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('is_prelim')->nullable()->default(false);
            $table->boolean('show_report')->nullable()->default(false);
            $table->foreign(['user_id'], 'fk_batch_labsection_approval_user_id_51dabab5')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['batch_id'], 'fk_batch_labsection_approval_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_labsection_approval');
    }
};
