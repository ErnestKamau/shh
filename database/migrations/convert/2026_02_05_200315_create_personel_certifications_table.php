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
        Schema::create('personel_certifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('personnel_id');
            $table->uuid('role_certification_id')->index('idx_personel_certifications_role_certification_id_29c279d4');
            $table->boolean('status')->default(false);
            $table->string('certificate');
            $table->string('certificate_body');
            $table->dateTime('certificate_date');
            $table->dateTime('expire_date');
            $table->string('edited_by')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personel_certifications');
    }
};
