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
        Schema::create('lab_section_approver_relation', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('lab_section_id');
            $table->uuid('user_id')->index('idx_lab_section_approver_relation_user_id_bfa2b24c');
            $table->string('title', 100)->nullable();
            $table->integer('parent_id')->nullable();
            $table->foreign(['user_id'], 'fk_lab_section_approver_relation_user_id_23a338dc')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_section_approver_relation');
    }
};
