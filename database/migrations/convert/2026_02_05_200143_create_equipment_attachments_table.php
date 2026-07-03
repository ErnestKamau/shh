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
        Schema::create('equipment_attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('attachment');
            $table->integer('upload_by');
            $table->integer('edit_by')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('equipment_id')->index('idx_equipment_attachments_equipment_id_a2d9ee48');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_attachments');
    }
};
