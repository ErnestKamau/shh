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
        Schema::create('solution_batch_history', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('solution_id')->index('solution_batch_history_solution_id_foreign');
            $table->string('batch_number');
            $table->date('prepared_date');
            $table->date('expiry_date');
            $table->decimal('quantity_prepared', 10);
            $table->unsignedBigInteger('uom_id');
            $table->enum('status', ['active', 'expired', 'recalled', 'consumed'])->default('active');
            $table->text('stability_notes')->nullable();
            $table->unsignedBigInteger('prepared_by');
            $table->unsignedBigInteger('preparation_id')->nullable();
            $table->timestamps();
            $table->foreign(['solution_id'], 'fk_solution_batch_history_solution_id_f5c4c63c')->references(['id'])->on('lab_sub_category')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solution_batch_history');
    }
};
