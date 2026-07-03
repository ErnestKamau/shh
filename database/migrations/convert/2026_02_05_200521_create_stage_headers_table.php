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
        if (Schema::hasTable('stage_headers')) {
            return;
        }
        Schema::create('stage_headers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('method_id');
            $table->uuid('analyte_id')->index('idx_stage_headers_analyte_id_75cd8f8f');
            $table->uuid('sample_type_id')->nullable()->index('idx_stage_headers_sample_type_id_92e4b72f');
            $table->integer('total_days');
            $table->boolean('is_multi_stage')->default(true);
            $table->timestamps();

            $table->index(['method_id', 'analyte_id'], 'idx_stage_headers_method_id_analyte_id_3ead32f9');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stage_headers');
    }
};
