<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_field_id');
            $table->string('label');
            $table->string('value');
            $table->integer('order_index')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->foreign('form_field_id')->references('id')->on('form_fields')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_field_options');
    }
};
