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
        if (Schema::hasTable('rating_details')) {
            return;
        }
        Schema::create('rating_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('rating_header_id')->index('idx_rating_details_rating_header_id_16b9f82c');
            $table->string('key');
            $table->string('label');
            $table->text('interpretation');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_details');
    }
};
