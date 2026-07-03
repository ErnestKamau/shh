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
        if (Schema::hasTable('skill_training_header_staff')) {
            return;
        }
        Schema::create('skill_training_header_staff', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('capability_matrix_role_id');
            $table->integer('training_header_id');
            $table->dateTime('deleted_at')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_training_header_staff');
    }
};
