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
        Schema::create('form_template_variables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('form_template_id');
            $table->string('name');
            $table->string('type'); // static, database, system
            $table->string('data_type'); // string, number, boolean, date, collection, record
            $table->json('config')->nullable();
            $table->json('validation_rules')->nullable();
            $table->uuid('created_by')->nullable()->index();
            $table->timestamps();
            
            $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
            $table->unique(['form_template_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_template_variables');
    }
};
