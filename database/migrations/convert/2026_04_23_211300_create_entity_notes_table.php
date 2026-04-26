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
        Schema::create('entity_notes', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('type');
            $table->string('description');
            $table->string('model');
            $table->integer('model_id');
            $table->integer('created_by');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_notes');
    }
};
