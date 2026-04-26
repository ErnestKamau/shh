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
            $table->integer('created_by');
            $table->text('notification');
            $table->integer('batch_id')->index('batch_id');
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
