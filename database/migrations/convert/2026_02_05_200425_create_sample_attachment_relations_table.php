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
        if (Schema::hasTable('sample_attachment_relations')) {
            return;
        }
        Schema::create('sample_attachment_relations', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('sample_id');
            $table->integer('attachment_id');
            $table->boolean('show_on_coa')->default(false);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_attachment_relations');
    }
};
