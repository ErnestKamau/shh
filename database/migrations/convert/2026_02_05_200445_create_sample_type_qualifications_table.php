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
        if (Schema::hasTable('sample_type_qualifications')) {
            return;
        }
        Schema::create('sample_type_qualifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('sample_id');
            $table->uuid('qualification_id')->index('idx_sample_type_qualifications_qualification_id_41d4f25f');
            $table->string('edited_by')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('is_mandatory')->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_type_qualifications');
    }
};
