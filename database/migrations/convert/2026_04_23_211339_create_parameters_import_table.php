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
        Schema::create('parameters_import', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('parameter', 150)->index('parameter');
            $table->string('method', 150);
            $table->string('analysis', 50);
            $table->integer('method_id')->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameters_import');
    }
};
