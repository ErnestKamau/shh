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
        if (Schema::hasTable('ser_testkit_worksheet_sample_relations')) {
            return;
        }
        Schema::create('ser_testkit_worksheet_sample_relations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ser_header_id')->index('idx_ser_testkit_worksheet_sample_relations_ser_header_c1814f8c');
            $table->string('test_name')->nullable();
            $table->string('kit_lot_number')->nullable();
            $table->string('wells_used')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            $table->primary(['id']);
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
