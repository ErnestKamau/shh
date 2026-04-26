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
        Schema::create('workorder_edits', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('workorder_id');
            $table->text('reason');
            $table->string('created_by');
            $table->integer('created_by_id');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workorder_edits');
    }
};
