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
            $table->uuid('created_by')->nullable()->index('idx_batch_notifications_created_by_58fb532d');
            $table->text('notification');
            $table->uuid('batch_id')->index('idx_batch_notifications_batch_id_c4e3fb73');
            $table->string('status')->nullable();
            $table->boolean('active')->default(true);
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
