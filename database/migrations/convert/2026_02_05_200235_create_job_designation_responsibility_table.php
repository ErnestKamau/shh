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
        if (Schema::hasTable('job_designation_responsibility')) {
            return;
        }
        Schema::create('job_designation_responsibility', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('job_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(false);
            $table->integer('edited_by')->nullable();
            $table->integer('config_id');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_designation_responsibility');
    }
};
