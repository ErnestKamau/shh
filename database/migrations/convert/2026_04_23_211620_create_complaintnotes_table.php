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
        Schema::create('complaintnotes', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->text('notes');
            $table->string('created_by');
            $table->uuid('complaint_id')->index('idx_complaintnotes_complaint_id_62af25c9');
            $table->string('type');
            $table->boolean('is_public')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->foreign(['complaint_id'], 'fk_complaintnotes_complaint_id_204e6b5b')->references(['id'])->on('complaints')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaintnotes');
    }
};
