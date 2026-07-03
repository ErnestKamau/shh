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
        if (Schema::hasTable('batch_comments')) {
            return;
        }
        Schema::create('batch_comments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('comments')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_batch_comments_created_by_cc250d58');
            $table->integer('reminder_for')->index('idx_batch_comments_reminder_for_18708097');
            $table->string('personnel_to_cc');
            $table->date('completed_at')->nullable();
            $table->timestamps();
            $table->uuid('sample_header_id')->nullable()->index('idx_batch_comments_sample_header_id_0671e058');
            $table->string('comment_type')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_comments');
    }
};
