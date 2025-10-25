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
            $table->id();
            $table->unsignedBigInteger('sample_header_id');
            $table->json('data_json'); // Stores: analysis_type_ids, company_sub_unit_id, sample_description, quantity, etc.
            $table->boolean('is_processed')->default(0);
            $table->timestamps();
            
            $table->foreign('sample_header_id')->references('id')->on('sample_headers')->onDelete('cascade');
            $table->index(['sample_header_id', 'is_processed']);
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
