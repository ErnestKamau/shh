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
        Schema::create('sample_imports', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('short_code')->nullable();
            $table->string('species')->nullable();
            $table->string('material_status')->nullable();
            $table->string('variety_name')->nullable();
            $table->string('sample_no')->nullable();
            $table->string('no_of_sample')->nullable();
            $table->string('no_of_pots_plants')->nullable();
            $table->string('standard_tests')->nullable();
            $table->string('compartiment_lot')->nullable();
            $table->string('planting_week')->nullable();
            $table->string('results')->nullable();
            $table->string('remarks')->nullable();
            $table->uuid('sample_header_id')->nullable()->index('idx_sample_imports_sample_header_id_eeff7a5e');
            $table->string('sample_condition', 500)->nullable();
            $table->foreign(['sample_header_id'], 'fk_sample_imports_sample_header_id_31a30e9f')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_imports');
    }
};
