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
        Schema::create('remedy_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('remedy_header_id')->index('remedy_details_remedy_header_id_foreign');
            $table->string('antibiotic');
            $table->enum('sensitivity', ['Sensitive', 'Resistant', 'Intermediate']);
            $table->string('dimension')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->foreign(['remedy_header_id'], 'fk_remedy_details_remedy_header_id_60f14724')->references(['id'])->on('remedy_headers')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remedy_details');
    }
};
