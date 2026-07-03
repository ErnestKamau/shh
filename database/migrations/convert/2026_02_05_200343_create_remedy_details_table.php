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
        if (Schema::hasTable('remedy_details')) {
            return;
        }
        Schema::create('remedy_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('remedy_header_id')->index('idx_remedy_details_remedy_header_id_88a69801');
            $table->string('antibiotic');
            $table->enum('sensitivity', ['Sensitive', 'Resistant', 'Intermediate']);
            $table->string('dimension')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remedy_details');
    }
};
