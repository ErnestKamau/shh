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
        Schema::create('lookup_table_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lookup_table_id');
            $table->json('keys'); // JSON object with key-value pairs for lookup keys
            $table->text('value');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['lookup_table_id']);
            $table->unique(['lookup_table_id', 'deleted_at']);
        });

        // Add foreign key constraints
        Schema::table('lookup_table_entries', function (Blueprint $table) {
            $table->foreign('lookup_table_id')->references('id')->on('lookup_tables')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookup_table_entries');
    }
};