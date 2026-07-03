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
        if (Schema::hasTable('skills_capability_matrix')) {
            return;
        }
        Schema::create('skills_capability_matrix', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('created_by')->nullable()->index('idx_skills_capability_matrix_created_by_fb539c3f');
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
