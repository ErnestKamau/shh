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
        Schema::create('solution_preparations', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('solution_id')->index('solution_preparations_solution_id_foreign');
            $table->string('batch_number');
            $table->unsignedBigInteger('prepared_by');
            $table->timestamp('prepared_at')->useCurrentOnUpdate()->useCurrent();
            $table->enum('status', ['preparing', 'completed', 'cancelled', 'failed'])->default('preparing');
            $table->text('notes')->nullable();
            $table->decimal('quantity_prepared', 10);
            $table->unsignedBigInteger('uom_id');
            $table->timestamps();
            $table->foreign(['solution_id'], 'fk_solution_preparations_solution_id_dd3390d4')->references(['id'])->on('lab_sub_category')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solution_preparations');
    }
};
