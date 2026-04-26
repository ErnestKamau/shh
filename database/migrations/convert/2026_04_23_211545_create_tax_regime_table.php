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
        Schema::create('tax_regime', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('registered_by')->nullable();
            $table->integer('value');
            $table->boolean('active')->default(false);
            $table->dateTime('end_date')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_regime');
    }
};
