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
            $table->uuid('lab_sub_category_id')->index('idx_lab_stock_movement_lab_sub_category_id_a004a8ba');
            $table->string('stock_type');
            $table->double('stock_in')->default(0);
            $table->double('stock_out')->default(0);
            $table->integer('uom_id');
            $table->uuid('created_by')->nullable()->index('idx_lab_stock_movement_created_by_07e5c5e9');
            $table->uuid('preparation_id')->nullable()->index('idx_lab_stock_movement_preparation_id_d4ac2471');
            $table->string('batch_number')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();

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
