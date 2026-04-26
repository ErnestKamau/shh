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
        Schema::create('sample_conditions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->boolean('active');
            $table->uuid('sample_type_id')->index('idx_sample_conditions_sample_type_id_e2717503');
            $table->timestamps();
            $table->string('short_name')->nullable();
            $table->integer('reporting_time')->nullable()->default(0);
            $table->foreign(['sample_type_id'], 'fk_sample_conditions_sample_type_id_ee4fc7a9')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_conditions');
    }
};
