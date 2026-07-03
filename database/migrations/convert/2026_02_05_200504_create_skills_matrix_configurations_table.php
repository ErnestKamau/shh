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
        Schema::create('skills_matrix_configurations', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('skills_matrix_id')->nullable();
            $table->integer('competence_area_id')->nullable();
            $table->integer('competence_type_id')->nullable();
            $table->integer('competence_description_id')->nullable();
            $table->string('name');
            $table->integer('parent')->default(0);
            $table->integer('level')->default(1);
            $table->timestamps();
            $table->boolean('active')->nullable()->default(true);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_matrix_configurations');
    }
};
