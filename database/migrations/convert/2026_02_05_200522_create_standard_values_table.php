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
        Schema::create('standard_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('code');
            $table->string('name');
            $table->boolean('status')->default(false);
            $table->integer('edited_by')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_values');
    }
};
