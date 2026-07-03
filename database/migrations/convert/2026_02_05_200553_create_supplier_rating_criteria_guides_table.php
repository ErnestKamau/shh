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
        if (Schema::hasTable('supplier_rating_criteria_guides')) {
            return;
        }
        Schema::create('supplier_rating_criteria_guides', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->integer('criteria_id');
            $table->double('lower_value');
            $table->double('upper_value');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_rating_criteria_guides');
    }
};
