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
        Schema::create('lab_sub_category', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('name');
            $table->string('image')->nullable();
            $table->integer('category_id');
            $table->integer('reporting_unit')->nullable();
            $table->string('rate')->nullable();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->double('stock')->nullable()->default(0);
            $table->string('current_batch_number')->nullable();
            $table->date('batch_prepared_date')->nullable();
            $table->date('batch_expiry_date')->nullable();
            $table->text('stability_notes')->nullable();
            $table->enum('batch_status', ['active', 'expired', 'recalled', 'consumed'])->default('active');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_sub_category');
    }
};
