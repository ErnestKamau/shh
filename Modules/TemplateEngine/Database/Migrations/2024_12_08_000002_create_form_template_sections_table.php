<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_template_sections', function (Blueprint $table) {
            $table->id();
            $table->uuid('form_template_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->string('type')->default('body'); // header, body, footer, custom
            $table->timestamps();
            
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_template_sections');
    }
};
