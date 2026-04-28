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
        Schema::create('role_certifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('certification_id');
            $table->string('edited_by')->nullable();
            $table->boolean('status')->default(false);
            $table->uuid('role_id')->index('idx_role_certifications_role_id_3092ef89');
            $table->boolean('is_mandatory')->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_certifications');
    }
};
