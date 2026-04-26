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
        Schema::create('batch_comments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('comments')->nullable();
            $table->integer('created_by');
            $table->integer('reminder_for')->index('reminder_for');
            $table->string('personnel_to_cc');
            $table->date('completed_at')->nullable();
            $table->timestamps();
            $table->uuid('sample_header_id')->nullable()->index('sample_header_id');
            $table->string('comment_type')->nullable();
            $table->foreign(['sample_header_id'], 'fk_batch_comments_sample_header_id_6ca4c5ac')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');

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
