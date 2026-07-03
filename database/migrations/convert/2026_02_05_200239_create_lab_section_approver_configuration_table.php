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
        if (Schema::hasTable('lab_section_approver_configuration')) {
            return;
        }
        Schema::create('lab_section_approver_configuration', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('user_id')->index('idx_lab_section_approver_configuration_user_id_b86a87e2');
            $table->string('lab_section_ids');
            $table->string('title')->nullable();

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
