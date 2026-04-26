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
        Schema::create('skills_capability_matrix', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('created_by');
            $table->integer('matrix_id');
            $table->string('name');
            $table->dateTime('deleted_at')->nullable();
            $table->boolean('status')->default(true);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_capability_matrix');
    }
};
