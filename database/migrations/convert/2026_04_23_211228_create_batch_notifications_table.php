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
        Schema::create('batch_notifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('position_id');
            $table->uuid('created_by')->nullable()->index('idx_batch_notifications_created_by');
            $table->text('notification');
            $table->uuid('batch_id')->index('idx_batch_notifications_batch_id');
            $table->string('status')->nullable();
            $table->boolean('active')->default(true);
            $table->foreign(['batch_id'], 'fk_batch_notifications_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_batch_notifications_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_notifications');
    }
};
