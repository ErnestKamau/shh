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
            $table->id();
            $table->foreignId('form_template_id')->constrained('form_templates')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // static, database, system
            $table->string('data_type'); // string, number, boolean, date, collection, record
            $table->json('config')->nullable();
            $table->json('validation_rules')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
            
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
