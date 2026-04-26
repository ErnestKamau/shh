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
        Schema::create('lab_stock_movement', function (Blueprint $table) {
            $table->uuid('id');
            $table->text('description')->nullable();
            $table->uuid('lab_sub_category_id')->index('idx_lab_stock_movement_lab_sub_category_id_a2a35b97');
            $table->string('stock_type');
            $table->double('stock_in')->default(0);
            $table->double('stock_out')->default(0);
            $table->integer('uom_id');
            $table->integer('created_by');
            $table->uuid('preparation_id')->nullable()->index('lab_stock_movement_preparation_id_foreign');
            $table->string('batch_number')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->foreign(['preparation_id'], 'fk_lab_stock_movement_preparation_id_9e41c528')->references(['id'])->on('solution_preparations')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_sub_category_id'], 'fk_lab_stock_movement_lab_sub_category_id_574a498a')->references(['id'])->on('lab_sub_category')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_stock_movement');
    }
};
