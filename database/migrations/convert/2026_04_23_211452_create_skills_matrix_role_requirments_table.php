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
        Schema::create('skills_matrix_role_requirments', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('skills_matrix_config_id')->nullable();
            $table->integer('skills_matrix_id')->nullable();
            $table->uuid('role_id')->nullable()->index('idx_skills_matrix_role_requirments_role_id_440c973a');
            $table->string('role_name', 100)->nullable();
            $table->integer('color_code')->nullable();
            $table->timestamps();
            $table->foreign(['role_id'], 'fk_skills_matrix_role_requirments_role_id_0ecc33c0')->references(['id'])->on('roles')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_matrix_role_requirments');
    }
};
