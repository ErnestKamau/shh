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
        Schema::create('sample_dates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->dateTime('date');
            $table->timestamps();
            $table->uuid('sample_header_id')->nullable()->index('idx_sample_dates_sample_header_id_3e058b19');
            $table->foreign(['sample_header_id'], 'fk_sample_dates_sample_header_id_0400ca62')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_dates');
    }
};
