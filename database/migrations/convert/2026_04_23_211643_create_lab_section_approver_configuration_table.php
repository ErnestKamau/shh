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
        Schema::create('lab_section_approver_configuration', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('user_id')->index('idx_lab_section_approver_configuration_user_id_9b98769f');
            $table->string('lab_section_ids');
            $table->string('title')->nullable();
            $table->foreign(['user_id'], 'fk_lab_section_approver_configuration_user_id_47c33b35')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_section_approver_configuration');
    }
};
