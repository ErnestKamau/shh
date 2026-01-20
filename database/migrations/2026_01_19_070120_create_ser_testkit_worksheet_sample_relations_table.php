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
        Schema::create('ser_testkit_worksheet_sample_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ser_header_id')->index();
            $table->string('test_name')->nullable();
            $table->string('kit_lot_number')->nullable();
            $table->string('wells_used')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('ser_header_id', 'fk_tstwc_hdr_id')->references('id')->on('ser_header_worksheet_sample_relations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ser_testkit_worksheet_sample_relations');
    }
};
