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
        Schema::create('stage_headers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('method_id');
            $table->uuid('analyte_id')->index('stage_headers_analyte_id_foreign');
            $table->uuid('sample_type_id')->nullable()->index('idx_stage_headers_sample_type_id_dd5681ff');
            $table->integer('total_days');
            $table->boolean('is_multi_stage')->default(true);
            $table->timestamps();

            $table->index(['method_id', 'analyte_id'], 'idx_stage_headers_method_id_analyte_id_1e21161c');
            $table->foreign(['analyte_id'], 'fk_stage_headers_analyte_id_19346a7c')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['method_id'], 'fk_stage_headers_method_id_46186784')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['sample_type_id'], 'fk_stage_headers_sample_type_id_9876e3c1')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('no action');
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
