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
            $table->uuid('batch_id')->index('idx_batch_labsection_approval_batch_id_1512f3f6');
            $table->uuid('user_id')->index('idx_batch_labsection_approval_user_id_f4d09d97');
            $table->string('title');
            $table->string('lab_section_ids');
            $table->boolean('status')->nullable()->default(false);
            $table->string('batch_status', 100)->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->text('remark')->nullable();
            $table->boolean('is_prelim')->nullable()->default(false);
            $table->boolean('show_report')->nullable()->default(false);

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
