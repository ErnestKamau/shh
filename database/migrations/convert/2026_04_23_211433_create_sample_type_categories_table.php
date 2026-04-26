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
        Schema::create('sample_type_categories', function (Blueprint $table) {
            $table->integer('id');
            $table->string('sample_type_category', 100);
            $table->boolean('active')->nullable()->default(true);
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->string('zoho_id', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_type_categories');
    }
};
