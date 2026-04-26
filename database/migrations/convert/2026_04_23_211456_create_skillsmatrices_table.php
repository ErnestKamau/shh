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
        Schema::create('skillsmatrices', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->integer('department_id')->default(0);
            $table->string('matrix_role_ids');
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skillsmatrices');
    }
};
