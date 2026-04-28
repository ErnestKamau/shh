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
        Schema::create('report_format_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('report_format_id');
            $table->string('key_name');
            $table->longText('text_value')->nullable();
            $table->timestamps();

            $table->unique(['report_format_id', 'key_name']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_format_details');
    }
};
