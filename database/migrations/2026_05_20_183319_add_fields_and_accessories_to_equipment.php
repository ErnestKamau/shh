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
        // Add new optional fields to equipment table
        Schema::table('equipment', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->date('installation_date')->nullable();
            $table->date('commissioning_date')->nullable();
            $table->string('detection_limit')->nullable();
            $table->string('tolerance_limit')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('warranty')->nullable();
            $table->string('environment')->nullable();
            $table->date('end_of_life')->nullable();
            $table->date('end_of_service')->nullable();
        });

        // Create equipment_accessories table
        Schema::create('equipment_accessories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('part_number')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
        });

        // Create equipment_spare_parts table
        Schema::create('equipment_spare_parts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('part_number')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_spare_parts');
        Schema::dropIfExists('equipment_accessories');

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_price',
                'installation_date',
                'commissioning_date',
                'detection_limit',
                'tolerance_limit',
                'supplier_name',
                'warranty',
                'environment',
                'end_of_life',
                'end_of_service'
            ]);
        });
    }
};
