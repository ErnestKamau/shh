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
        Schema::create('preparation_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->bigInteger('preparation_id');
            $table->integer('step_number');
            $table->string('step_name');
            $table->text('description');
            $table->bigInteger('ingredient_id')->nullable();
            $table->decimal('quantity_used', 10)->nullable();
            $table->bigInteger('uom_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->bigInteger('completed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preparation_steps');
    }
};
