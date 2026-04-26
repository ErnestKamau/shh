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
        Schema::create('sample_detail_staging', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_header_id');
            $table->longText('data_json');
            $table->boolean('is_processed')->default(false);
            $table->timestamps();

            $table->index(['sample_header_id', 'is_processed'], 'idx_sample_detail_staging_sample_header_id_is_processe_61266019');
            $table->foreign(['sample_header_id'], 'fk_sample_detail_staging_sample_header_id_e4a389df')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_detail_staging');
    }
};
