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
        Schema::create('batch_ammendments', function (Blueprint $table) {
            $table->uuid('id')->index('id');
            $table->timestamps();
            $table->uuid('batch_id')->index('idx_batch_ammendments_batch_id');
            $table->uuid('created_by_id')->nullable()->index('idx_batch_ammendments_created_by_id');
            $table->text('reason');
            $table->string('samples');
            $table->string('report_url');
            $table->integer('version_number')->default(0);
            // $table->foreign(['batch_id'], 'fk_batch_ammendments_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            // $table->foreign(['created_by_id'], 'fk_batch_ammendments_created_by_id')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_ammendments');
    }
};
