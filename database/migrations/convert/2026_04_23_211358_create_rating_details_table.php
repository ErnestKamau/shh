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
        Schema::create('rating_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('rating_header_id')->index('rating_details_rating_header_id_foreign');
            $table->string('key');
            $table->string('label');
            $table->text('interpretation');
            $table->timestamps();
            $table->foreign(['rating_header_id'], 'fk_rating_details_rating_header_id_0066c540')->references(['id'])->on('rating_headers')->onUpdate('no action')->onDelete('cascade');
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
